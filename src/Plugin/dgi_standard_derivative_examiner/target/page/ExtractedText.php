<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\page;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Page extracted text file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "page.extracted_text",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ExtractedText",
 *   type = "extracted_text",
 *   default_action = "get_ocr_from_image",
 * )
 */
class ExtractedText extends TargetPluginBase {

}
