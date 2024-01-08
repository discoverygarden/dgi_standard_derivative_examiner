<?php

namespace Drupal\dgi_standard_derivative_examiner\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * @Annotation
 */
class DgiStandardDerivativeExaminerModel extends Plugin {

  public string $id;
  public string $uri;

}
