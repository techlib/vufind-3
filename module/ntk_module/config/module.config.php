<?php
namespace ntk_module\Module\Configuration;

$config = array(
    'controllers' => array(
        'factories' => array(
            'record' => 'ntk_module\Controller\Factory::getRecordController',
        ),
        'invokables' => array(
            'ajax' => 'ntk_module\Controller\AjaxController',
            'cover' => 'ntk_module\Controller\CoverController',
            'my-research' => 'ntk_module\Controller\MyResearchController',
        ),
    ),
    'vufind' => array(
        'plugin_managers' => array(
            'content_covers' => array(
                'invokables' => array(
                    'alephimagecovers' => 'ntk_module\Content\Covers\AlephImageCovers',
                ),
            ),
            'ils_driver' => array(
                'factories' => array(
                    'aleph' => 'ntk_module\ILS\Driver\Factory::getAleph',
                ),
            ),
            'recommend' => array(
                'factories' => array(
                    'authorityrecommend' => 'ntk_module\Recommend\Factory::getAuthorityRecommend',
                ),
            ),
            'recorddriver' => array(
                'factories' => array(
                    'missing' => 'ntk_module\RecordDriver\Factory::getMissing',
                    'solrauth' => 'ntk_module\RecordDriver\Factory::getSolrAuth',
                    'solrmarc' => 'ntk_module\RecordDriver\Factory::getSolrMarc',
                ),
            ),
            'recordtab' => array(
                'factories' => array(
                    'holdingsils' => 'ntk_module\RecordTab\Factory::getHoldingsILS',
                ),
            ),
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            'VuFind\ILSHoldLogic' => 'ntk_module\Service\Factory::getILSHoldLogic',
        ),
    ),
);

// Define static routes -- Controller/Action strings
$staticRoutes = array(
    'MyResearch/CheckedOutHistory'
);

// Build static routes
foreach ($staticRoutes as $route) {
    list($controller, $action) = explode('/', $route);
    $routeName = str_replace('/', '-', strtolower($route));
    $config['router']['routes'][$routeName] = array(
        'type' => 'Zend\Mvc\Router\Http\Literal',
        'options' => array(
            'route'    => '/' . $route,
            'defaults' => array(
                'controller' => $controller,
                'action'     => $action,
            )
        )
    );
}

return $config;
