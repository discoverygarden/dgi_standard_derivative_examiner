<?php

namespace Drupal\dgi_standard_derivative_examiner\Drush\Commands;

use Consolidation\AnnotatedCommand\Attributes\HookSelector;
use Drupal\Component\DependencyInjection\ContainerInterface;
use Drupal\controlled_access_terms\Plugin\Field\FieldType\AuthorityLink;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\dgi_standard_derivative_examiner\ModelPluginManagerInterface;
use Drupal\dgi_standard_derivative_examiner\UnknownModelException;
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
  #[CLI\Option(name: 'model-uri', description: 'One (or more, comma-separated) model URIs to which to filter.')]
  #[CLI\Option(name: 'source-use-uri', description: 'One (or more, comma-separated) media use URIs to which to filter.')]
  #[CLI\Option(name: 'dest-use-uri', description: 'One (or more, comma-separated) media use URIs to which to filter.')]
  #[CLI\Option(name: 'fields', description: 'Comma-separated listing of fields.')]
  #[HookSelector(name: 'islandora-drush-utils-user-wrap')]
  public function derive(array $options = [
    'dry-run' => self::OPT,
    'model-uri' => self::REQ,
    'source-use-uri' => self::REQ,
    'dest-use-uri' => self::REQ,
    'fields' => 'nid,model_uri,model_plugin,target_plugin,target_uri,expected,exists,message',
  ]) : void {
    $parse_uris = function (string $key) use ($options) : array {
      $uris = array_map('trim', explode(',', $options[$key]));
      return array_combine($uris, $uris);
    };

    $uris['model'] = isset($options['model-uri']) ? $parse_uris('model-uri') : [];
    $uris['source-use'] = isset($options['source-use-uri']) ? $parse_uris('source-use-uri') : [];
    $uris['dest-use'] = isset($options['dest-use-uri']) ? $parse_uris('dest-use-uri') : [];

    $do_uri = function (string $type, string $uri) use ($uris, $options) {
      return !isset($options["{$type}-uri"]) || array_key_exists($uri, $uris[$type]);
    };

    $fields = explode(',', $options['fields']);
    $emit_row = function (
      string $nid,
      string $model_uri,
      string $model_plugin,
      ?string $target_plugin = NULL,
      ?string $target_uri = NULL,
      ?bool $expected = NULL,
      ?bool $exists = NULL,
      string $message = '',
    ) use ($fields) {
      $row = [];
      foreach ($fields as $field) {
        // XXX: Variable variable shenanigans, so yes, the doubled `$` is
        // intentional.
        $row[] = $$field;
      }

      fputcsv(STDOUT, $row);
    };

    foreach ($this->getNodes() as $node) {
      $this->logger()->debug('Processing {node}', ['node' => $node->id()]);
      foreach (static::getModelUri($node) as $uri) {
        if (!$do_uri('model', $uri)) {
          continue;
        }

        try {
          /** @var \Drupal\dgi_standard_derivative_examiner\ModelInterface $model */
          $model = $this->modelPluginManager->getInstance(['uri' => $uri]);

          $targets = $model->getDerivativeTargets();
          $this->logger()
            ->debug('Found {uri} for {node} with {count} targets', [
              'node' => $node->id(),
              'uri' => $uri,
              'count' => count($targets),
            ]);
          foreach ($targets as $target) {
            if (
              !$do_uri('source-use', $target->getPluginDefinition()['source_uri']) ||
              !$do_uri('dest-use', $target->getPluginDefinition()['uri'])
            ) {
              continue;
            }
            $expected = $target->expected($node);
            $exists = $target->exists($node);
            $to_trigger = $expected && !$exists;

            if (!$options['dry-run'] && $to_trigger) {
              $target->derive($node);
            }

            $emit_row(
              $node->id(),
              $uri,
              $model->getPluginId(),
              $target->getPluginId(),
              $target->getPluginDefinition()['uri'],
              $expected,
              $exists,
              (
              $to_trigger ?
                ($options['dry-run'] ? 'To trigger.' : 'Triggered.') :
                'No need to trigger.'
              ),
            );
          }
        }
        catch (UnknownModelException) {
          $emit_row(
            $node->id(),
            $uri,
            'unknown',
            message: 'Unknown model; unknown targets.',
          );
        }
      }
    }
  }

  /**
   * Helper; load up the nodes.
   *
   * Reads all the rows passed via STDIN, expecting the first column to
   * represent a node ID.
   *
   * Also, should be mockable, if necessary.
   *
   * @return \Drupal\node\NodeInterface[]
   *   Array of nodes to process.
   */
  protected function getNodes() : array {
    $node_ids = [];

    while ($row = fgetcsv(STDIN)) {
      [$node_id] = $row;
      $node_ids[] = $node_id;
    }

    return $this->nodeStorage->loadMultiple(array_filter(array_map('trim', $node_ids)));
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
