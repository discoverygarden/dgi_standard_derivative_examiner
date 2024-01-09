<?php

namespace Drupal\dgi_standard_derivative_examiner;

/**
 * Model interface.
 */
interface ModelInterface {

  /**
   * Get the defined model URI.
   *
   * @return string
   *   The model URI.
   */
  public function getModelUri() : string;

  /**
   * Get targets for the given model.
   *
   * @return \Drupal\dgi_standard_derivative_examiner\TargetInterface[]
   *   An array of targets.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   *   If the plugin could not be created.
   */
  public function getDerivativeTargets() : array;

}
