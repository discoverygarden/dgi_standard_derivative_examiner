<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\dgi_standard_derivative_examiner\TargetInterface;

/**
 * Describe the absence of a term.
 *
 * @internal
 */
class TargetTermAbsentException extends DerivativeExaminerTargetException {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    ?TargetInterface $target = NULL,
    readonly public string $uri = '',
  ) {
    parent::__construct(
      match (TRUE) {
        !empty($message) => $message,
        !empty($this->uri) => "The term with URI {$this->uri} could not be found.",
        default => '',
      },
      $code,
      $previous,
      $target,
    );
  }

}
