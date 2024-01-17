<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Model interface.
 */
interface ModelInterface extends PluginInspectionInterface {

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

  /**
   * Given some properties, attempt to load a target plugin.
   *
   * @param array $options
   *   An associative array of properties, possibly containing:
   *   - source_uri: The source media use URI.
   *   - uri: The target/destination media use URI.
   *   - id: The name of the plugin to load, if known.
   *   More generally, anything on the DgiStandardDerivativeExaminerTarget
   *   annotation.
   *
   * @return \Drupal\dgi_standard_derivative_examiner\TargetInterface
   *   The first matching target, if one could be found.
   *
   * @throws \Drupal\dgi_standard_derivative_examiner\UnknownTargetException
   *   Throw if a target plugin could not be identified.
   */
  public function getDerivativeTarget(array $options) : TargetInterface;

}
