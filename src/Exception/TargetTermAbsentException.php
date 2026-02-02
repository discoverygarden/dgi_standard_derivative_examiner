<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

/**
 * Describe the absence of a term.
 */
class TargetTermAbsentException extends DerivativeExaminerException {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
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
    );
  }

}
