<?php

namespace Drupal\dgi_standard_derivative_examiner\Utility;

use Drupal\node\NodeInterface;

/**
 * Examiner service interface.
 */
interface ExaminerInterface {

  /**
   * Validates an entity to ensure all expected derivatives are present.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node being examined.
   *
   * @return array
   *   A key value array where the key is the nid of the node missing
   *   derivatives and the values contain:
   *   - bundle (string): The expected bundle of the media.
   *   - use_uri (string): The media use URI that should be present.
   *   - message (string): A human-readable string clarifying what is missing.
   */
  public function examine(NodeInterface $node) : array;

}
