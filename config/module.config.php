<?php
return array(
    'router' => array(
        'routes' => array(
            'admin' => array(
                'child_routes' => array(
                    'libra-article-image-zooming' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/libra-article-image-zooming/',
                            'defaults' => array(
                                'module'     => 'libra-article',
                                'controller' => 'index',
                                'action'     => 'index',
                            ),
                        ),
                    ),
                ),
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'libra-article-image-zooming/admin-index' => 'LibraArticleImageZooming\Controller\AdminIndexController',
        ),
    ),
    'view_manager' => array(
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
    ),
);
