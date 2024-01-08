<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\model;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dgi_standard_derivative_examiner\TargetPluginManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ModelPluginBase extends PluginBase implements ContainerFactoryPluginInterface {

  protected TargetPluginManagerInterface $targetPluginManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->targetPluginManager = $container->get("plugin.manager.dgi_standard_derivative_examiner.target.{$plugin_id}");

    return $instance;
  }

  public function getModelUri() : string {
    return $this->pluginDefinition['uri'];
  }

  public function getDerivativeTargets() : array {
    $targets = [];

    foreach (array_keys($this->targetPluginManager->getDefinitions()) as $id) {
      $targets[$id] = $this->targetPluginManager->createInstance($id);
    }

    return $targets;
  }

}
