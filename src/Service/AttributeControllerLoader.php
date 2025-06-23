<?php

namespace WechatMiniProgramServerMessageBundle\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\RouteCollection;
use WechatMiniProgramServerMessageBundle\Controller\ServerController;

class AttributeControllerLoader
{
    public function __construct(
        #[Autowire(service: 'routing.loader.attribute')]
        private readonly AttributeClassLoader $controllerLoader
    ) {
    }

    public function autoload(): RouteCollection
    {
        $collection = new RouteCollection();
        $collection->addCollection($this->controllerLoader->load(ServerController::class));
        
        return $collection;
    }
}