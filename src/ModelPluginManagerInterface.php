<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Model plugin manager interface.
 */
interface ModelPluginManagerInterface extends PluginManagerInterface {

  /**
   * Get instance given a model URI.
   *
   * @param string $uri
   *   A model URI for which to fetch a plugin instance.
   *
   * @return \Drupal\dgi_standard_derivative_examiner\ModelInterface
   *   The model instance for the given URI.
   */
  public function createInstanceFromUri(string $uri) : ModelInterface;

}
