<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner;

use Drupal\Core\Action\ActionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dgi_standard_derivative_examiner\TargetInterface;
use Drupal\islandora\IslandoraContextManager;
use Drupal\islandora\IslandoraUtils;
use Drupal\media\MediaInterface;
use Drupal\media\MediaStorage;
use Drupal\node\NodeInterface;
use Drupal\system\ActionConfigEntityInterface;
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
   * @var \Drupal\taxonomy\TermInterface
   */
  protected TermInterface $term;

  /**
   * Islandora's extended context manager service.
   *
   * @var \Drupal\islandora\IslandoraContextManager
   */
  protected IslandoraContextManager $contextManager;

  /**
   * The derivative action.
   *
   * @var \Drupal\system\ActionConfigEntityInterface|null
   */
  protected ?ActionConfigEntityInterface $action;

  /**
   * The media storage service.
   *
   * XXX: Ideally, could reference an interface, but such does not exist.
   *
   * @var \Drupal\media\MediaStorage
   */
  protected MediaStorage $mediaStorage;

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
      $entity_type_manager->getStorage('action')->load($plugin_definition['default_action']) :
      NULL;
    $instance->mediaStorage = $entity_type_manager->getStorage('media');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function expected(NodeInterface $node) : bool {
    // In the majority of cases, we expect the defined items to exist, if the
    // given source exists.
    return (bool) $this->getSource($node);
  }

  /**
   * {@inheritDoc}
   */
  public function exists(NodeInterface $node) : bool {
    return !empty($this->utils->getMediaReferencingNodeAndTerm($node, $this->term));
  }

  /**
   * {@inheritDoc}
   */
  public function derive(NodeInterface $node) : void {
    $this->action->execute($this->getSource($node));
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
