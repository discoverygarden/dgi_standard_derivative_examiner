<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\digital_document;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Digital document thumbnail file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "digital_document.thumbnail",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ThumbnailImage",
 *   type = "image",
 *   default_action =
 *    "digital_document_generate_a_thumbnail_from_an_original_file",
 * )
 */
class Thumbnail extends TargetPluginBase {

}
