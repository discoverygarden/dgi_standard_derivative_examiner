<?php

namespace Drupal\dgi_standard_derivative_examiner\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Derived target info.
 *
 * @Annotation
 */
class DgiStandardDerivativeExaminerTarget extends Plugin {

  /**
   * Target machine name.
   *
   * @var string
   */
  public string $id;

  /**
   * Target source URI.
   *
   * @var string
   */
  public string $source_uri;

  /**
   * Media use URI of the given target.
   *
   * @var string
   */
  public string $uri;

  /**
   * Media type bundle of the given target.
   *
   * @var string
   */
  public string $type;

  /**
   * The name of an action that should (eventually) populate the given target.
   *
   * @var string|null
   */
  public ?string $default_action;

}
