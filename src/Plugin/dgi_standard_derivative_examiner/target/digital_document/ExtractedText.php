<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\digital_document;

/**
 * Digital document extracted text file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "digital_document.extracted_text",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ExtractedText",
 *   type = "extracted_text",
 *   default_action = "get_ocr_from_image"
 * )
 */
class ExtractedText extends AbstractUnderivedSource {

}
