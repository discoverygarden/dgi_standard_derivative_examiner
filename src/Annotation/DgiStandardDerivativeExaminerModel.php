<?php

namespace Drupal\dgi_standard_derivative_examiner\Annotation;

use Drupal\Component\Annotation\Plugin;

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

}
