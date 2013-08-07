<?php

namespace SpeckShipping;

class Module
{
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
        return array(
            'speck_shipping' => array(
                'cost_modifiers' => array(
                    'incremental_qty' => '\SpeckShipping\Entity\CostModifier\IncrementalQty',
                ),
            ),
            'controllers' => array(
                'invokables' => array(
                    'shipping' => 'SpeckShipping\Controller\Shipping',
                ),
            ),
            'router' => array(
                'routes' => array(
                    'shipping' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/shipping[/[:cartId]]',
                            'defaults' => array(
                                'controller' => 'shipping',
                                'action'     => 'index'
                            ),
                        ),
                    ),
                ),
            ),
            'service_manager' => array(
                'invokables' => array(
                    'speckshipping_shipping_service' => 'SpeckShipping\Service\Shipping',
                ),
                'factories' => array(
                    'speckshipping_config' => function ($sm) {
                        $config = $sm->get('Config');
                        return $config['speck_shipping'];
                    },
                ),
            ),
        );
    }

    public function onBootstrap($e)
    {
        $app = $e->getParam('application');
        $em  = $app->getEventManager()->getSharedManager();
        $sl  = $app->getServiceManager();

        $em->attach(
            'SpeckCheckout\Strategy\Step\UserInformation',
            'setComplete',
            function ($e) use ($sl) {
            }
        );

        $em->attach(
            'SpeckCatalogCart\Service\CartService',
            'persistItem',
            function ($e) use ($sl) {
            }
        );
        
        $em->attach(
            'SpeckCatalogCart\Service\CartService',
            'addItemToCart',
            function ($e) use ($sl) {
            }
        );

        $em->attach(
            'SpeckShipping\Service\Shipping',
            'getShippingCost',
            function ($e) use ($sl) {
                $handler = new \SpeckShipping\Event\Shipping();
                $handler->setServiceLocator($sl);
                $handler->cartShippingCost($e);
            }
        );

        $em->attach(
            'SpeckShipping\Service\Shipping',
            'getShippingClassCost',
            function ($e) use ($sl) {
                $handler = new \SpeckShipping\Event\Shipping();
                $handler->setServiceLocator($sl);
                $handler->shippingClassCostModifiers($e);
            }
        );
    }
}
