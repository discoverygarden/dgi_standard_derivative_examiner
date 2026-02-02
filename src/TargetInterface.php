<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\node\NodeInterface;

/**
 * Derivation target interface.
 */
interface TargetInterface extends PluginInspectionInterface {

  /**
   * Determine if the given derived target exists.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if the derived file exists; otherwise, FALSE.
   *
   * @throws \Drupal\dgi_standard_derivative_examiner\Exception\TargetTermAbsentException
   *   Thrown when a term describing the target derivative could not be found.
   */
  public function exists(NodeInterface $node) : bool;

  /**
   * Determine if the given source exists and is readable.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if the source file exists and is readable; otherwise, potentially
   *   FALSE, though usually an exception will be thrown.
   *
   * @throws \Drupal\dgi_standard_derivative_examiner\Exception\SourceException
   *   Thrown when there is an exceptional state detected with the given source.
   */
  public function sourceExists(NodeInterface $node) : bool;

  /**
   * Given a node, check if we expect the given derivative to exist.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if there should be a derivative; otherwise, FALSE.
   *
   * @throws \Drupal\dgi_standard_derivative_examiner\Exception\TargetTermAbsentException
   *   Thrown when a term describing the target derivative could not be found.
   */
  public function expected(NodeInterface $node) : bool;

  /**
   * Perform the derivation action.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to which to relate the derivative.
   *
   * @throws \Drupal\dgi_standard_derivative_examiner\Exception\UnknownDerivativeTargetPlugin
   *   Thrown if an unknown derivative action/target plugin is encountered.
   * @throws \Drupal\dgi_standard_derivative_examiner\Exception\SourceException
   *   Potentially propagated from our pre-flight ::sourceExists() check.
   */
  public function derive(NodeInterface $node) : void;

}
