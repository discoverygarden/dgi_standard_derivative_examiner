<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\Exception\PluginException;

/**
 * Exception thrown when attempting to acquire targets for an unknown model.
 */
class UnknownModelException extends PluginException {

  /**
   * Constructor.
   */
  public function __construct(array $plugin_definition) {
    parent::__construct("Unknown model. Def: " . var_export($plugin_definition, TRUE));
  }

}
