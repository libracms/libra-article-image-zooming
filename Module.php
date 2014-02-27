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
        $sharedManager->attach('LibraArticle\Controller\ArticleController', 'dispatch', function($e) {
            //add fancybox to article view
            $controller = $e->getTarget();
            $controller->getEventManager()->attach('view', function($e) {
                $controller = $e->getTarget();
                $phpRenderer = $controller->getServiceLocator()->get('ViewRenderer');
                $basePath = $phpRenderer->basePath();
                $phpRenderer->headLink()->appendStylesheet($basePath . '/vendor/libra/fancybox-assets/source/jquery.fancybox.css');
                $phpRenderer->headScript()->appendFile($basePath . '/vendor/libra/fancybox-assets/source/jquery.fancybox.js');
                $phpRenderer->inlineScript()->appendScript(
                    "jQuery('document').ready(function($) {
                        $('.content a.zoom').fancybox();
                    });"
                );
            });
        }, 100);

        $sharedManager->attach('LibraArticle\Controller\AdminArticleController', 'dispatch', function($e) {
            $controller = $e->getTarget();
            $clearAnchors = function($e) {
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
            $addAnchors = function($e) {
                $article = $e->getParam('article');
                $controller = $e->getTarget();
                $content = $article->getContent();
                /** @var $zooming Zooming */
                $zooming = $controller->getServiceLocator()->get('LibraArticleImageZooming\Model\Zooming');
                $newContent = $zooming->convert($content);
                if ($newContent === false) return false; //don't save. Has no image
                $article->setContent($newContent);

                // Return true to save it
                return true;
            };
            $controller->getEventManager()->attach('get', $clearAnchors);
            $controller->getEventManager()->attach('save.post', $addAnchors);
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
