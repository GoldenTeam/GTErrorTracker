<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'GTErrorTracker\Controller\Event' => 'GTErrorTracker\Controller\EventController',
            'GTErrorTracker\Controller\Cron' => 'GTErrorTracker\Controller\CronController'
        ),
    ),
    'console' => array(
        'router' => array(
            'routes' => array(
                'cron_delete' => array(
                    'options' => array(
                        'route' => 'cron_delete',//php index.php cron_delete
                        'defaults' => array(
                            '__NAMESPACE__' => 'GTErrorTracker\Controller',
                            'controller' => 'cron',
                            'action' => 'delete'
                        ),
                    ),
                ),
            )
        )
    ),

    'controller_plugins' => array(
        'invokables' => array(
            'GTHead' => 'GTErrorTracker\Controller\Plugin\GTHead',
            'GTResult' => 'GTErrorTracker\Controller\Plugin\GTResult',
            'GTGateway' => 'GTErrorTracker\Controller\Plugin\GTGateway',
            'GTParam' => 'GTErrorTracker\Controller\Plugin\GTParam',
        )
    ),
    'router' => array(
        'routes' => array(
            'gtevent' => array(
                'type' => 'segment',
                'options' => array(
                    'route' => '/gtevent[/:action][/page/:page][/event_id/:event_logger_id]',
                    'constraints' => array(
                        'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                        'event_logger_id' => '[0-9]*',
                        'page' => '[0-9]*',
                    ),
                    'defaults' => array(
                        'controller' => 'GTErrorTracker\Controller\Event',
                        'action' => 'index',
                    ),
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'not_found_template' => 'error/404',
        'exception_template' => 'error/index',
//        'template_map' => array(
//            'layout/layout' => __DIR__ . '/../view/layout/nutrition.phtml',
//            'error/404' => __DIR__ . '/../view/error/404.phtml',
//            'error/index' => __DIR__ . '/../view/error/index.phtml',
//        ),
        'template_path_stack' => array(
            'gt-error-tracker' => __DIR__ . '/../view',
        ),
        'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
    'view_helpers' => array(
        'invokables' => array(
            'GTHtml' => 'GTErrorTracker\View\Helper\GTHtml',
            'GTTxt' => 'GTErrorTracker\View\Helper\GTTxt',
        )
    )
);