<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\video;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Video service file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "video.service_file",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ServiceFile",
 *   type = "video",
 *   default_action = "video_generate_a_service_file_from_an_original_file",
 * )
 */
class ServiceFile extends TargetPluginBase {

}
