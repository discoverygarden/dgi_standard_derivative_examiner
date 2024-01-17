<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\dgi_standard_derivative_examiner\Annotation\DgiStandardDerivativeExaminerModel;

/**
 * Model plugin manager service.
 */
class ModelPluginManager extends DefaultPluginManager implements ModelPluginManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      'Plugin/dgi_standard_derivative_examiner/model',
      $namespaces,
      $module_handler,
      ModelInterface::class,
      DgiStandardDerivativeExaminerModel::class,
    );

    $this->mapper = new DefMapper($this);
    $this->alterInfo('dgi_standard_derivative_examiner_model_plugin_info');
  }

  /**
   * {@inheritDoc}
   */
  public function getFallbackPluginId($plugin_id, array $configuration = []) {
    return '__undefined_model__';
  }

}
