<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\page;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Page hocr file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "page.hocr",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "https://discoverygarden.ca/use#hocr",
 *   type = "file",
 *   default_action = "generate_hocr_from_an_image",
 * )
 */
class Hocr extends TargetPluginBase {

}
