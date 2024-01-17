<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Service provider.
 */
class DgiStandardDerivativeExaminerServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritDoc}
   */
  public function register(ContainerBuilder $container) {
    $container->addCompilerPass(new ModelTargetPluginManagerExtensionPass());
  }

}
