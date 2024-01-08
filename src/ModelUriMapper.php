<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\Mapper\MapperInterface;

/**
 * Model plugin URI mapper.
 */
class ModelUriMapper implements MapperInterface {

  /**
   * Memoized mapping of URIs to plugin IDs.
   * @var array
   */
  protected array $mapping = [];

  /**
   * Constructor.
   */
  public function __construct(
    protected ModelPluginManagerInterface $modelPluginManager,
  ) {}

  /**
   * {@inheritDoc}
   */
  public function getInstance(array $options) {
    if (!isset($options['uri'])) {
      throw new \LogicException('A "uri" option is required.');
    }

    $uri = $options['uri'];

    if (!isset($this->mapping[$uri])) {
      foreach ($this->modelPluginManager->getDefinitions() as $id => $definition) {
        if ($definition['uri'] === $uri) {
          $this->mapping[$uri] = $id;
          break;
        }
      }
    }

    return $this->modelPluginManager->createInstance($this->mapping[$uri]);
  }

}
