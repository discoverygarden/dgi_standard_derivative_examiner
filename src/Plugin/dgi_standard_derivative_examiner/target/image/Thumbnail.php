<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\image;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Image thumbnail file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "image.thumbnail",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ThumbnailImage",
 *   type = "image",
 *   default_action = "image_generate_a_thumbnail_from_an_original_file",
 * )
 */
class Thumbnail extends TargetPluginBase {

}
