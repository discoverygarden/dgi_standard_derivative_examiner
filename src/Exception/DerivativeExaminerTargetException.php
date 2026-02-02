<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\dgi_standard_derivative_examiner\TargetInterface;

/**
 * General derivative examiner exceptions.
 */
abstract class DerivativeExaminerTargetException extends \RuntimeException {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    public readonly ?TargetInterface $target = NULL,
  ) {
    parent::__construct($message, $code, $previous);
  }

}
