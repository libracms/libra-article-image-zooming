<?php
/**
 * Created by  duke.
 * Date: 28/09/12
 * Time: 19:09
 */

namespace LibraArticleImageZooming\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use LibraArticleImageZooming\Model\Zooming;

class ZoomingModelFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options = $serviceLocator->get('Config');
        //$options = $options['libra_article_image_zooming']['configuration'];
        return new Zooming($options);
    }

}
