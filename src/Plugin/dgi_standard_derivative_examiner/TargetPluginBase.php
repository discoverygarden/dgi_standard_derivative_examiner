<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\dgi_standard_derivative_examiner\TargetInterface;
use Drupal\islandora\ContextProvider\NodeContextProvider;
use Drupal\islandora\IslandoraContextManager;
use Drupal\islandora\IslandoraUtils;
use Drupal\islandora\Plugin\Action\AbstractGenerateDerivativeBase;
use Drupal\islandora\Plugin\ContextReaction\DerivativeReaction;
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
   * The action storage service.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected EntityStorageInterface $actionStorage;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);

    $instance->utils = $container->get('islandora.utils');
    $instance->term = $instance->utils->getTermForUri($plugin_definition['uri']);
    $instance->actionStorage = $container->get('entity_type.manager')->getStorage('action');

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public function expected(NodeInterface $node) : bool {
    // In the majority of cases, we expect the defined items to exist.
    return TRUE;
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
    foreach ($this->getRelevantActions($node) as $action) {
      $action->execute($node);
    }
  }

  /**
   * Helper; identify those relevant actions from context.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to obtain actions.
   *
   * @return \Generator
   *   Generated applicable actions.
   */
  protected function getRelevantActions(NodeInterface $node) {
    $provider = new NodeContextProvider($node);
    $provided = $provider->getRuntimeContexts([]);
    $this->contextManager->evaluateContexts($provided);

    foreach ($this->contextManager->getActiveReactions('derivative') as $reaction) {
      if (!$reaction instanceof DerivativeReaction) {
        continue;
      }

      $action_ids = $reaction->getConfiguration()['actions'];
      $actions = $this->actionStorage->loadMultiple($action_ids);
      foreach ($actions as $action) {
        if (!$action instanceof AbstractGenerateDerivativeBase) {
          continue;
        }
        $action_config = $action->getConfiguration();
        if (
          $action_config['derivative_term_uri'] === $this->getPluginDefinition()['uri'] &&
          $action_config['destination_media_type'] === $this->getPluginDefinition()['type']
        ) {
          yield $action;
        }
      }
    }
  }

}
