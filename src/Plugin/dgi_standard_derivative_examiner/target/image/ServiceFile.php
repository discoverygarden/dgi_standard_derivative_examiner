<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\image;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Image service file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "image.serivce_file",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ServiceFile",
 *   type = "image",
 *   default_action = "image_generate_a_jp2000_from_an_original_file",
 * )
 */
class ServiceFile extends TargetPluginBase {

}
