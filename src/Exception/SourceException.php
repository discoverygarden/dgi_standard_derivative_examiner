<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\dgi_standard_derivative_examiner\TargetInterface;
use Drupal\media\MediaInterface;

/**
 * Represent exceptional circumstances around source media.
 */
class SourceException extends DerivativeExaminerTargetException {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    ?TargetInterface $target = NULL,
    readonly public ?MediaInterface $media = NULL,
  ) {
    parent::__construct($message, $code, $previous, $target);
  }

}
