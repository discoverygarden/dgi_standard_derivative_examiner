<?php

namespace Drupal\dgi_standard_derivative_examiner\Utility;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\islandora\IslandoraUtils;
use Drupal\node\NodeInterface;

/**
 * Service class to determine whether a node is missing derivatives.
 */
class Examiner {

  /**
   * Lays out expectation given DGI's standard content type.
   *
   * Contains:
   *   - An array where the key is the model URI and the values are an array
   * representing expected media derivatives for a model. The array contains:
   *     - bundle (string): The expected media bundle for the derivative.
   *     - use_uri (string): The media_use taxonomy term external URI of the
   *     expected derivative.
   */
  private const DERIVATIVE_MATRIX = [
    // Audio.
    'http://purl.org/coar/resource_type/c_18cc' => [
      [
        'bundle' => 'audio',
        'use_uri' => 'http://pcdm.org/use#ServiceFile',
      ],
    ],
    // Binary.
    'http://purl.org/coar/resource_type/c_1843' => [],
    // Collection.
    'http://purl.org/dc/dcmitype/Collection' => [],
    // Compound.
    'http://vocab.getty.edu/aat/300242735' => [],
    // Digital Document.
    'https://schema.org/DigitalDocument' => [],
    // Image.
    'http://purl.org/coar/resource_type/c_c513' => [
      [
        'bundle' => 'image',
        'use_uri' => 'http://pcdm.org/use#ThumbnailImage',
      ],
      [
        'bundle' => 'image',
        'use_uri' => 'http://pcdm.org/use#ServiceFile',
      ],
    ],
    // Newspaper.
    'https://schema.org/Newspaper' => [],
    // Page.
    'http://id.loc.gov/ontologies/bibframe/part' => [
      [
        'bundle' => 'image',
        'use_uri' => 'http://pcdm.org/use#ThumbnailImage',
      ],
      [
        'bundle' => 'extracted_text',
        'use_uri' => 'http://pcdm.org/use#ExtractedText',
      ],
      [
        'bundle' => 'image',
        'use_uri' => 'http://pcdm.org/use#ServiceFile',
      ],
      [
        'bundle' => 'file',
        'use_uri' => 'https://discoverygarden.ca/use#hocr',
      ],
    ],
    // Paged Content.
    'https://schema.org/Book' => [],
    // Publication Issue.
    'https://schema.org/PublicationIssue' => [],
    // Video.
    'http://purl.org/coar/resource_type/c_12ce' => [
      [
        'bundle' => 'image',
        'use_uri' => 'http://pcdm.org/use#ThumbnailImage',
      ],
      [
        'bundle' => 'video',
        'use_uri' => 'http://pcdm.org/use#ServiceFile',
      ],
    ],
  ];

  /**
   * The Drupal entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Islandora's utilities.
   *
   * @var \Drupal\islandora\IslandoraUtils
   */
  protected IslandoraUtils $islandoraUtils;

  /**
   * Constructor for the derivative examiner.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Drupal entity type manager.
   * @param \Drupal\islandora\IslandoraUtils $islandora_utils
   *   Islandora's utility functions.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, IslandoraUtils $islandora_utils) {
    $this->entityTypeManager = $entity_type_manager;
    $this->islandoraUtils = $islandora_utils;
  }

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
  public function examine(NodeInterface $node) {
    $missing = [];
    // First ensure that the entity being examined is grounded in Islandora.
    if ($node->hasField(IslandoraUtils::MODEL_FIELD)) {
      $field = $node->get(IslandoraUtils::MODEL_FIELD);
      if (!$field->isEmpty()) {
        $model_term = $field->referencedEntities()[0];
        $model_external_uri = $model_term->get(IslandoraUtils::EXTERNAL_URI_FIELD)
          ->getString();

        // The node must have an original file to proceed.
        $original_file_term = $this->islandoraUtils->getTermForUri('http://pcdm.org/use#OriginalFile');
        $original_file_medias = $this->islandoraUtils->getMediaReferencingNodeAndTerm($node, $original_file_term);
        if (!empty($original_file_medias)) {
          // Verify that the original file media has a non-zero file.
          $original_file_mid = reset($original_file_medias);
          $original_file_media = $this->entityTypeManager->getStorage('media')->load($original_file_mid);
          $original_file_fid = $original_file_media->getSource()->getSourceFieldValue($original_file_media);
          if (empty($original_file_fid)) {
            $missing[] = ['message' => 'Missing original file entity.'];
          }
          else {
            $file = $this->entityTypeManager->getStorage('file')->load($original_file_fid);
            if (!$file || $file->getSize() <= 0) {
              $missing[] = ['message' => 'Original file seems to be missing or corrupt.'];
            }
            else {
              // Finally validate against the derivative matrix.
              foreach (self::DERIVATIVE_MATRIX[$model_external_uri] as $values) {
                $media_use_terms = $this->entityTypeManager->getStorage('taxonomy_term')
                  ->loadByProperties([IslandoraUtils::EXTERNAL_URI_FIELD => $values['use_uri']]);
                // Only expect one here.
                $media_use_term = reset($media_use_terms);
                $derivatives = $this->islandoraUtils->getMediaReferencingNodeAndTerm($node, $media_use_term);
                if (empty($derivatives)) {
                  $missing[] = $values + ['message' => 'Missing derivative media and file.'];
                }
                else {
                  $derivative = reset($derivatives);
                  $derivative_media = $this->entityTypeManager->getStorage('media')->load($derivative);
                  $derivative_fid = $derivative_media->getSource()->getSourceFieldValue($derivative_media);
                  if (empty($derivative_fid)) {
                    $missing[] = $values + ['message' => 'Missing derivative file.'];
                  }
                  else {
                    $file = $this->entityTypeManager->getStorage('file')->load($derivative_fid);
                    if (!$file || $file->getSize() <= 0) {
                      $missing[] = $values + ['message' => 'Derivative file entity seems to be missing or corrupt.'];
                    }
                  }
                }
              }
            }
          }
        }
      }
    }
    return $missing;
  }

}
