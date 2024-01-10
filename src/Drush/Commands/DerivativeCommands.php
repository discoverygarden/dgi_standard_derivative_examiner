<?php

namespace Drupal\dgi_standard_derivative_examiner\Drush\Commands;

use Consolidation\AnnotatedCommand\Attributes\HookSelector;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\controlled_access_terms\Plugin\Field\FieldType\AuthorityLink;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_standard_derivative_examiner\ModelPluginManagerInterface;
use Drupal\islandora\IslandoraUtils;
use Drupal\node\NodeInterface;
use Drupal\node\NodeStorageInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;

/**
 * Derivative command class.
 */
class DerivativeCommands extends DrushCommands {

  /**
   * Node storage service.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected NodeStorageInterface $nodeStorage;

  /**
   * Constructor.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ModelPluginManagerInterface $modelPluginManager,
  ) {
    parent::__construct();
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) : self {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.dgi_standard_derivative_examiner.model'),
    );
  }

  /**
   * Given node IDs on stdin, report on or derive derivatives.
   *
   * Outputs to STDOUT.
   *
   * @param array $options
   *   Options, see attributes for details.
   */
  #[CLI\Command(name: 'dgi-standard-derivative-examiner:derive', aliases: ['dsde:d'])]
  #[CLI\Option(name: 'dry-run', description: 'Flag to avoid making changes.')]
  #[HookSelector(name: 'islandora-drush-utils-user-wrap')]
  public function derive(array $options = [
    'dry-run' => self::OPT,
  ]) : void {
    $node_ids = [];
    while ($row = fgetcsv(STDIN)) {
      [$node_id] = $row;
      $node_ids[] = $node_id;
    }
    /** @var \Drupal\node\NodeInterface[] $nodes */
    $nodes = $this->nodeStorage->loadMultiple(array_filter(array_map('trim', $node_ids)));

    foreach ($nodes as $node) {
      $this->logger()->debug('Processing {node}', ['node' => $node->id()]);
      foreach (static::getModelUri($node) as $uri) {
        $model = $this->modelPluginManager->createInstanceFromUri($uri);
        $targets = $model->getDerivativeTargets();
        $this->logger()->debug('Found {uri} for {node} with {count} targets', [
          'node' => $node->id(),
          'uri' => $uri,
          'count' => count($targets),
        ]);
        foreach ($targets as $target) {
          $expected = $target->expected($node);
          $exists = $target->exists($node);
          $to_trigger = $expected && !$exists;

          if (!$options['dry-run'] && $to_trigger) {
            $target->derive($node);
          }

          fputcsv(STDOUT, [
            $node->id(),
            $uri,
            $model->getPluginId(),
            $model->getPluginDefinition()['uri'],
            $target->getPluginId(),
            $target->getPluginDefinition()['uri'],
            $expected,
            $exists,
            (
              $to_trigger ?
                ($options['dry-run'] ? 'To trigger.' : 'Triggered.') :
                'No need to trigger.'
            ),
          ]);
        }
      }
    }
  }

  /**
   * Get model URI(s) for the given node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node for which to obtain the model URI(s).
   *
   * @return string[]
   *   The URI(s).
   */
  protected static function getModelUri(NodeInterface $node) : array {
    $uris = [];

    /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $model_reference_list */
    $model_reference_list = $node->get(IslandoraUtils::MODEL_FIELD);
    /** @var \Drupal\taxonomy\TermInterface $referencedEntity */
    foreach ($model_reference_list->referencedEntities() as $referencedEntity) {
      $uri_list = $referencedEntity->get(IslandoraUtils::EXTERNAL_URI_FIELD);
      /** @var \Drupal\controlled_access_terms\Plugin\Field\FieldType\AuthorityLink $uri */
      foreach ($uri_list as $uri) {
        $uris[] = $uri->get(AuthorityLink::mainPropertyName())->getValue();
      }
    }

    return $uris;
  }

}
