<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 *
 */
class ModelTargetPluginManagerExtensionPass implements CompilerPassInterface {

  /**
   * {@inheritDoc}
   */
  public function process(ContainerBuilder $container) {
    $model_plugin_manager = $container->get('plugin.manager.dgi_standard_derivative_examiner.model');

    foreach ($model_plugin_manager->getDefinitions() as $id => $definition) {
      $container->register("plugin.manager.dgi_standard_derivative_examiner.target.{$id}", TargetPluginManager::class)
        ->setArguments([
          $id,
          new Reference('container.namespaces'),
          new Reference('module_handler'),
        ]);
    }
  }

}
