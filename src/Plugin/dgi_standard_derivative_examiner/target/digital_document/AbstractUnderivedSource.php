<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\digital_document;

use Drupal\dgi_standard_derivative_examiner\Exception\SourceException;
use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;
use Drupal\node\NodeInterface;

/**
 * Abstract base for un-derived sources.
 */
abstract class AbstractUnderivedSource extends TargetPluginBase {

  /**
   * {@inheritDoc}
   */
  public function expected(NodeInterface $node) : bool {
    return parent::expected($node) && $this->expectedBasedOnFileType($node);
  }

  /**
   * Check if we actually expect things, based on the actual file type.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to test.
   *
   * @return bool
   *   TRUE if expected; otherwise, FALSE.
   */
  protected function expectedBasedOnFileType(NodeInterface $node) : bool {
    $source_media = $this->getSource($node);

    if (!$source_media) {
      throw new SourceException("Unable to determine source media.");
    }

    $source = $source_media?->getSource();
    $source_file_id = $source?->getSourceFieldValue($source_media);
    if (!$source_file_id) {
      throw new SourceException("Unable to determine source property ID.", media: $source_media);
    }
    /** @var \Drupal\file\FileInterface $source_file */
    $source_file = $this->fileStorage->load($source_file_id);

    if (!$source_file) {
      throw new SourceException("Unable to load file entity (ID: {$source_file_id}).", media: $source_media);
    }

    // TRUE if looks like PDF; otherwise, FALSE.
    return $source_file->getMimeType() === 'application/pdf' || strtolower(pathinfo($source_file->getFilename(), PATHINFO_EXTENSION)) === 'pdf';
  }

}
