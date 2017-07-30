<?php

namespace AppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EventSubscriberPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $collectorDefinition = $container->getDefinition('biz.event_subscriber.collector');

        $taggedServices = $container->findTaggedServiceIds('biz.event.subscriber');

        foreach ($taggedServices as $id => $tags) {
            $def = $container->getDefinition($id);
            if (is_subclass_of($def->getClass(), 'Symfony\Component\EventDispatcher\EventSubscriberInterface')) {
                $collectorDefinition->addMethodCall('add', array(new Reference($id)));
            }
        }
    }
}
