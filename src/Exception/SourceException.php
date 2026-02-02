<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\media\MediaInterface;

/**
 * Represent exceptional circumstances around source media.
 */
class SourceException extends \Exception {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    readonly public ?MediaInterface $media = NULL,
  ) {
    parent::__construct($message, $code, $previous);
  }

}
