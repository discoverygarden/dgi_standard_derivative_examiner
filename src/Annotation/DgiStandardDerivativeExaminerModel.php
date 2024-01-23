<?php

namespace Drupal\dgi_standard_derivative_examiner\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\dgi_standard_derivative_examiner\TargetPluginManager;

/**
 * Model plugin info.
 *
 * @Annotation
 */
class DgiStandardDerivativeExaminerModel extends Plugin {

  /**
   * Model machine-name/ID, for use with Drupal in general.
   *
   * @var string
   */
  public string $id;

  /**
   * Model URI.
   *
   * @var string
   */
  public string $uri;

  /**
   * The name of the class managing target plugins.
   *
   * @var string
   */
  public string $targetManagerClass = TargetPluginManager::class;

}
