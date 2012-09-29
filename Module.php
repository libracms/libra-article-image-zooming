<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraArticleImageZooming;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use LibraArticleImageZooming\Model\Zooming;

/**
 * Description of Module
 *
 * @author duke
 */
class Module implements
    AutoloaderProviderInterface,
    ConfigProviderInterface,
    ServiceProviderInterface
{
    public function onBootstrap(MvcEvent $e)
    {
        $sharedManager = $e->getApplication()->getEventManager()->getSharedManager();
        $sharedManager->attach('LibraArticle\Controller\AdminArticleController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $func = function($e) {
                $article = $e->getParam('article');
                $controller = $e->getTarget();
                $content = $article->getContent();
                /** @var $zooming Zooming */
                $zooming = $controller->getServiceLocator()->get('LibraArticleImageZooming\Model\Zooming');
                $newContent = $zooming->convert($content);
                if ($newContent === false) return false; //don't save. Has no image
                $article->setContent($newContent);
                /** @var $controller \Zend\Mvc\Controller\AbstractActionController */
                $controller->getEntityManager()->flush($article);
                return true;
            };
            $funcPre = function($e) {
                $data = $e->getParam('data');
                /** @var $controller \Zend\Mvc\Controller\AbstractActionController */
                $controller = $e->getTarget();
                $content = $data['content'];

                $zooming = $controller->getServiceLocator()->get('LibraArticleImageZooming\Model\Zooming');
                $newContent = $zooming->revert($content);
                if ($newContent === false) return false; //don't save. Has no image
                $data['content'] = $newContent;
                $e->setParam('data', $data);
                return true;
            };
            $controller->getEventManager()->attach('get', $funcPre);
            $controller->getEventManager()->attach('save.post', $func);
            //$controller->getEventManager()->attach('create.post', $up);
        }, 100);
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
                //'LibraArticleImageZooming\Model\Zooming' => 'LibraArticleImageZooming\Model\Zooming',
            ),
            'factories' => array(
                'LibraArticleImageZooming\Model\Zooming' => 'LibraArticleImageZooming\Service\ZoomingModelFactory',
            ),
        );
    }
    
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

}
