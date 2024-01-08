<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\dgi_standard_derivative_examiner\Annotation\DgiStandardDerivativeExaminerTarget;

class TargetPluginManager extends DefaultPluginManager implements TargetPluginManagerInterface {

  /**
   * Constructor.
   */
  public function __construct(
    string $type,
    \Traversable $namespaces,
    ModuleHandlerInterface $module_handler,
  ) {
    parent::__construct(
      "Plugin/dgi_standard_derivative_examiner/model/{$type}",
      $namespaces,
      $module_handler,
      TargetInterface::class,
      DgiStandardDerivativeExaminerTarget::class,
    );
  }

}
