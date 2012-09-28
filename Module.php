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
            $func = function($e) {
                $article = $e->getParam('article');
                $content = $article->getContent();
                $newContent = Zooming::zooming($content);
                if ($newContent === false) return false; //don't save. Has no image
                $article->setContent($newContent);
                $controller = $e->getTarget();
                $controller->getEntityManager()->flush($article);
                return true;
            };
            $controller = $e->getTarget();
            $controller->getEventManager()->attach('save.post', $func);
            //$controller->getEventManager()->attach('create.post', $up);
        }, 100);
    }

    public function getServiceConfig()
    {
        return array(
            'invokables' => array(
            ),
            'factories' => array(
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
