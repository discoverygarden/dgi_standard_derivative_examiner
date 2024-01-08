<?php

namespace Drupal\dgi_standard_derivative_examiner\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class DgiStandardDerivativeExaminerTarget extends Plugin {

  public string $id;
  public string $use;
  public string $type;

}
