<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\dgi_standard_derivative_examiner\Annotation\DgiStandardDerivativeExaminerTarget;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Target plugin manager service.
 */
class TargetPluginManager extends DefaultPluginManager implements TargetPluginManagerInterface, ContainerInjectionInterface {

  /**
   * Constructor.
   */
  public function __construct(
    string $type,
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      "Plugin/dgi_standard_derivative_examiner/target/{$type}",
      $namespaces,
      $module_handler,
      TargetInterface::class,
      DgiStandardDerivativeExaminerTarget::class,
    );

    $this->mapper = new DefMapper($this);
    $this->alterInfo("dgi_standard_derivative_examiner_{$type}_target_plugin_info");
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, string $type = '') : self {
    return new static(
      $type,
      $container->get('container.namespaces'),
      $container->get('module_handler'),
    );
  }

}
