<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;

/**
 * Derivation target interface.
 */
interface TargetInterface {

  /**
   * Determine if the given derived target exists.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if the derived file exists; otherwise, FALSE.
   */
  public function exists(NodeInterface $node) : bool;

  /**
   * Given a node, check if we expect the given derivative to exist.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to check.
   *
   * @return bool
   *   TRUE if there should be a derivative; otherwise, FALSE.
   */
  public function expected(NodeInterface $node) : bool;

  /**
   * Perform the derivation action.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to which to relate the derivative.
   */
  public function derive(NodeInterface $node) : void;

}
