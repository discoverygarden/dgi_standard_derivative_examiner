<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\model;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\ModelPluginBase;
use Drupal\dgi_standard_derivative_examiner\UnknownModelException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Represent an undefined model.
 *
 * @DgiStandardDerivativeExaminerModel(
 *    id = "__undefined_model__",
 *    uri = "about:invalid",
 *  )
 */
class UndefinedModel extends ModelPluginBase {

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return parent::create($container, $configuration, $plugin_id, $configuration + $plugin_definition);
  }

  /**
   * {@inheritDoc}
   */
  public function getDerivativeTargets() : array {
    throw new UnknownModelException($this->getPluginDefinition());
  }

}
