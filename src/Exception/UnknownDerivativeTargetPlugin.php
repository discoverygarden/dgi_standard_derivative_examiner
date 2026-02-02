<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\Core\Action\ActionInterface;

/**
 * Unknown plugin, unknown how to invoke it.
 *
 * What parameters to pass?
 */
class UnknownDerivativeTargetPlugin extends \Exception {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    public readonly ?ActionInterface $action,
  ) {
    parent::__construct(
      $message ?: "Unknown derivative target {$this->action?->getPluginId()}.",
      $code,
      $previous,
    );
  }

}
