<?php

namespace Drupal\dgi_standard_derivative_examiner\Exception;

use Drupal\Core\Action\ActionInterface;
use Drupal\dgi_standard_derivative_examiner\TargetInterface;

/**
 * Unknown plugin, unknown how to invoke it.
 *
 * What parameters to pass?
 *
 * @internal
 */
class UnknownDerivativeTargetPlugin extends DerivativeExaminerTargetException {

  /**
   * Constructor.
   */
  public function __construct(
    string $message = "",
    int $code = 0,
    ?\Throwable $previous = NULL,
    ?TargetInterface $target = NULL,
    public readonly ?ActionInterface $action = NULL,
  ) {
    parent::__construct(
      $message ?: "Unknown derivative target {$this->action?->getPluginId()}.",
      $code,
      $previous,
      $target,
    );
  }

}
