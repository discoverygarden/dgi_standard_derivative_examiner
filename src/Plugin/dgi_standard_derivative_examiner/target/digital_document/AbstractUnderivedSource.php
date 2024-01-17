<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\digital_document;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;
use Drupal\file\FileStorageInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract base for un-derived sources.
 */
abstract class AbstractUnderivedSource extends TargetPluginBase {

  /**
   * The file storage service.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected FileStorageInterface $fileStorage;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);

    $instance->fileStorage = $container->get('entity_type.manager')->getStorage('file');

    return $instance;
  }

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

    $source = $source_media->getSource();
    $source_file_id = $source->getSourceFieldValue($source_media);
    /** @var \Drupal\file\FileInterface $source_file */
    $source_file = $this->fileStorage->load($source_file_id);

    // TRUE if looks like PDF; otherwise, FALSE.
    return $source_file->getMimeType() === 'application/pdf' || strtolower(pathinfo($source_file->getFilename(), PATHINFO_EXTENSION)) === 'pdf';
  }

}
