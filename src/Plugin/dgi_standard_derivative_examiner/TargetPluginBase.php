<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dgi_standard_derivative_examiner\Exception\SourceException;
use Drupal\dgi_standard_derivative_examiner\Exception\TargetTermAbsentException;
use Drupal\dgi_standard_derivative_examiner\Exception\UnknownDerivativeTargetPlugin;
use Drupal\dgi_standard_derivative_examiner\TargetInterface;
use Drupal\file\FileStorageInterface;
use Drupal\islandora\IslandoraContextManager;
use Drupal\islandora\IslandoraUtils;
use Drupal\islandora\Plugin\Action\AbstractGenerateDerivative;
use Drupal\islandora\Plugin\Action\AbstractGenerateDerivativeMediaFile;
use Drupal\media\MediaInterface;
use Drupal\media\MediaStorage;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract target plugin.
 */
abstract class TargetPluginBase extends PluginBase implements TargetInterface, ContainerFactoryPluginInterface {

  /**
   * The Islandora utility service.
   *
   * XXX: Ideally, could be referenced by interface; however, such an interface
   * does not exist.
   *
   * @var \Drupal\islandora\IslandoraUtils
   */
  protected IslandoraUtils $utils;

  /**
   * The source term.
   *
   * @var \Drupal\taxonomy\TermInterface
   */
  protected TermInterface $sourceTerm;

  /**
   * The term for this target.
   *
   * @var \Drupal\taxonomy\TermInterface|null
   */
  protected ?TermInterface $term;

  /**
   * Islandora's extended context manager service.
   *
   * @var \Drupal\islandora\IslandoraContextManager
   */
  protected IslandoraContextManager $contextManager;

  /**
   * The derivative action.
   *
   * @var \Drupal\Core\Action\ActionInterface|null
   */
  protected ?ActionInterface $action;

  /**
   * The media storage service.
   *
   * XXX: Ideally, could reference an interface, but such does not exist.
   *
   * @var \Drupal\media\MediaStorage
   */
  protected MediaStorage $mediaStorage;

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
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->utils = $container->get('islandora.utils');
    $entity_type_manager = $container->get('entity_type.manager');
    $instance->sourceTerm = $instance->utils->getTermForUri($plugin_definition['source_uri']);
    $instance->term = $instance->utils->getTermForUri($plugin_definition['uri']);
    $instance->action = $plugin_definition['default_action'] ?
      $entity_type_manager->getStorage('action')->load($plugin_definition['default_action'])?->getPlugin() :
      NULL;
    if ($instance->action) {
      assert($plugin_definition['uri'] === $instance->action->getConfiguration()['derivative_term_uri']);
    }
    $instance->mediaStorage = $entity_type_manager->getStorage('media');
    $instance->fileStorage = $entity_type_manager->getStorage('file');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function expected(NodeInterface $node) : bool {
    // In the majority of cases, we expect the defined items to exist, if the
    // given source exists.
    if (!$this->term) {
      throw new TargetTermAbsentException(uri: $this->getPluginDefinition()['uri'] ?? '(unknown URI)');
    }
    return (bool) $this->getSource($node);
  }

  /**
   * {@inheritDoc}
   */
  public function exists(NodeInterface $node) : bool {
    if (!$this->term) {
      throw new TargetTermAbsentException(uri: $this->getPluginDefinition()['uri'] ?? '(unknown URI)');
    }
    $media = $this->utils->getMediaReferencingNodeAndTerm($node, $this->term);
    return !empty($media);
  }

  /**
   * {@inheritDoc}
   */
  public function sourceExists(NodeInterface $node) : bool {
    if (!($source_media = $this->getSource($node))) {
      return FALSE;
    }

    $fid = $source_media->getSource()->getSourceFieldValue($source_media);
    /** @var \Drupal\file\FileInterface|null $file */
    if (!$fid) {
      throw new SourceException("Media source property appears empty (media ID: {$source_media->id()}).", media: $source_media);
    }
    if (!($file = $this->fileStorage->load($fid))) {
      throw new SourceException("Failed to load source file entity ({$fid}) referenced by media property.", media: $source_media);
    }
    if (!file_exists($file->getFileUri())) {
      throw new SourceException("Source file does not appear to exist (ID: {$fid}; URI: {$file->getFileUri()}).", media: $source_media);
    }
    if (!is_readable($file->getFileUri())) {
      throw new SourceException("Source file does not appear to be readable (ID: {$fid}; URI: {$file->getFileUri()}).", media: $source_media);
    }
    if ($file->getSize() <= 0) {
      throw new SourceException("Source file appears to be empty (ID: {$fid}; URI: {$file->getFileUri()}).", media: $source_media);
    }

    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function derive(NodeInterface $node) : void {
    if ($this->action instanceof AbstractGenerateDerivative) {
      $this->action->execute($node);
      return;
    }
    if ($this->action instanceof AbstractGenerateDerivativeMediaFile) {
      $this->action->execute($this->getSource($node));
      return;
    }
    throw new UnknownDerivativeTargetPlugin(action: $this->action);
  }

  /**
   * Helper; get the source media.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to obtain the source media.
   *
   * @return \Drupal\media\MediaInterface|null
   *   The source media if present; otherwise, NULL.
   */
  protected function getSource(NodeInterface $node) : ?MediaInterface {
    $sources = $this->utils->getMediaReferencingNodeAndTerm($node, $this->sourceTerm);
    assert(count($sources) < 2);
    return count($sources) > 0 ? $this->mediaStorage->load(reset($sources)) : NULL;
  }

}
