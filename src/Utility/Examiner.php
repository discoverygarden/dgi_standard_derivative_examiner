<?php

namespace Drupal\dgi_standard_derivative_examiner\Utility;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileStorageInterface;
use Drupal\islandora\IslandoraUtils;
use Drupal\media\MediaStorage;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Service class to determine whether a node is missing derivatives.
 */
class Examiner implements ExaminerInterface {

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
   * The file storage service.
   *
   * @var \Drupal\file\FileStorageInterface
   */
  protected FileStorageInterface $fileStorage;

  /**
   * The media storage service.
   *
   * XXX: Ideally, could reference an interface; however, this does not appear
   * to have one?
   *
   * @var \Drupal\media\MediaStorage
   */
  protected MediaStorage $mediaStorage;

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
    $this->fileStorage = $this->entityTypeManager->getStorage('file');
    $this->mediaStorage = $this->entityTypeManager->getStorage('media');
    $this->islandoraUtils = $islandora_utils;
  }

  /**
   * Memoized original file taxonomy term.
   *
   * @var \Drupal\taxonomy\TermInterface|null
   */
  protected ?TermInterface $originalFileTerm;

  /**
   * Acquire the original file taxonomy term.
   *
   * @return \Drupal\taxonomy\TermInterface
   *   The original file taxonomy term.
   */
  protected function getOriginalFileTerm() : TermInterface {
    if (!isset($this->originalFileTerm)) {
      $this->originalFileTerm = $this->islandoraUtils->getTermForUri('http://pcdm.org/use#OriginalFile');
      if (!$this->originalFileTerm) {
        throw new \LogicException('Missing original file taxonomy term!');
      }
    }

    return $this->originalFileTerm;
  }

  /**
   * Memoized media use terms.
   *
   * Mapping of media URIs to either:
   * - The target term, if found; or,
   * - FALSE, if we could not find the target term.
   *
   * @var \Drupal\taxonomy\TermInterface[]|null[]
   */
  protected array $useTerms = [];

  /**
   * Get term for the given media use.
   *
   * @param string $uri
   *   The media use URI for which to fetch the term.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   The term if found; otherwise, NULL.
   */
  protected function getMediaUseTerm(string $uri) : ?TermInterface {
    if (!array_key_exists($uri, $this->useTerms)) {
      $this->useTerms[$uri] = $this->islandoraUtils->getTermForUri($uri);
    }

    return $this->useTerms[$uri];
  }

  /**
   * {@inheritDoc}
   */
  public function examine(NodeInterface $node) : array {
    $missing = [];
    // First ensure that the entity being examined is grounded in Islandora.
    if (!$node->hasField(IslandoraUtils::MODEL_FIELD)) {
      throw new \InvalidArgumentException("The given node (nid {$node->id()}) does not have the " . IslandoraUtils::MODEL_FIELD . " field.");
    }

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $field */
    $field = $node->get(IslandoraUtils::MODEL_FIELD);
    assert(!$field->isEmpty(), 'Missing model!?');

    foreach ($field->referencedEntities() as $model_term) {
      $model_external_uri = $model_term->get(IslandoraUtils::EXTERNAL_URI_FIELD)
        ->getString();

      // The node must have an original file to proceed.
      $original_file_medias = $this->islandoraUtils->getMediaReferencingNodeAndTerm($node, $this->getOriginalFileTerm());
      if (!empty($original_file_medias)) {
        // Verify that the original file media has a non-zero file.
        $original_file_mid = reset($original_file_medias);

        /** @var \Drupal\media\MediaInterface $original_file_media */
        $original_file_media = $this->mediaStorage->load($original_file_mid);
        $original_file_fid = $original_file_media->getSource()->getSourceFieldValue($original_file_media);
        if (empty($original_file_fid)) {
          $missing[] = ['message' => 'Missing original file entity.'];
        }
        else {
          /** @var \Drupal\file\FileInterface|null $file */
          $file = $this->fileStorage->load($original_file_fid);
          if (!$file) {
            $missing[] = ['message' => 'Original file seems to be missing.'];
          }
          elseif ($file->getSize() <= 0) {
            $missing[] = ['message' => 'Original file seems to be corrupt.'];
          }
          else {
            // Finally validate against the derivative matrix.
            foreach (self::DERIVATIVE_MATRIX[$model_external_uri] as $values) {
              $media_use_term = $this->getMediaUseTerm($values['use_uri']);
              // The case the term URI doesn't exist.
              if (!$media_use_term) {
                $missing[] = $values + ['message' => 'Missing media use term.'];
                continue;
              }

              $derivatives = $this->islandoraUtils->getMediaReferencingNodeAndTerm($node, $media_use_term);
              if (empty($derivatives)) {
                $missing[] = $values + ['message' => 'Missing derivative media and file.'];
                continue;
              }
              elseif (($count = count($derivatives)) > 1) {
                $missing[] = $values + ['message' => "Found {$count} derivatives, but only one is expected."];
              }

              foreach ($derivatives as $derivative) {
                /** @var \Drupal\media\MediaInterface $derivative_media */
                $derivative_media = $this->mediaStorage->load($derivative);
                $derivative_fid = $derivative_media->getSource()
                  ->getSourceFieldValue($derivative_media);
                if (empty($derivative_fid)) {
                  $missing[] = $values + ['message' => 'Missing derivative file.'];
                }
                else {
                  /** @var \Drupal\file\FileInterface|null $file */
                  $file = $this->fileStorage->load($derivative_fid);
                  if (!$file) {
                    $missing[] = $values + ['message' => 'Derivative file entity seems to be missing.'];
                  }
                  elseif ($file->getSize() <= 0) {
                    $missing[] = $values + ['message' => 'Derivative file entity seems to be corrupt.'];
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
