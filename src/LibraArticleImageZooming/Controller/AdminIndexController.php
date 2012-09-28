<?php

/*
 * eJoom.com
 * This source file is subject to the new BSD license.
 */

namespace LibraArticleImageZooming\Controller;

use Zend\Mvc\Controller\ActionController;
use Zend\View\Model\ViewModel;

/**
 * Description of IndexController
 *
 * @author duke
 */
class AdminIndexController extends ActionController
{
    public function indexAction()
    {
        return new ViewModel(array(
            'class' => __CLASS__,
        ));
    }
}
