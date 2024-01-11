<?php

namespace Drupal\dgi_standard_derivative_examiner;

use Drupal\Component\Plugin\Mapper\MapperInterface;
use Drupal\Component\Plugin\PluginManagerInterface;

/**
 * Model plugin URI mapper.
 */
class DefMapper implements MapperInterface {

  /**
   * Memoized mapping of URIs to plugin IDs.
   *
   * @var array
   */
  protected array $mapping = [];

  /**
   * Constructor.
   */
  public function __construct(
    protected PluginManagerInterface $pluginManager,
  ) {}

  /**
   * {@inheritDoc}
   */
  public function getInstance(array $options) {
    assert(count($options) > 0);
    $key = static::buildKey($options);

    if (!isset($this->mapping[$key])) {
      foreach ($this->pluginManager->getDefinitions() as $id => $definition) {
        foreach ($options as $k => $v) {
          if ($definition[$k] != $v) {
            continue 2;
          }
        }

        $this->mapping[$key] = $this->pluginManager->createInstance($id);
        break;
      }
    }

    return $this->mapping[$key];
  }

  /**
   * Helper; build out mapping keys.
   *
   * @param array $options
   *   Options for which to build out a key.
   *
   * @return string
   *   The built key.
   */
  protected static function buildKey(array $options) : string {
    ksort($options);
    $parts = [];
    foreach ($options as $key => $value) {
      $parts[] = "{$key}:{$value}";
    }
    return implode(',', $parts);
  }

}
