<?php

namespace Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\target\audio;

use Drupal\dgi_standard_derivative_examiner\Plugin\dgi_standard_derivative_examiner\TargetPluginBase;

/**
 * Audio service file.
 *
 * @DgiStandardDerivativeExaminerTarget(
 *   id = "audio.service_file",
 *   source_uri = "http://pcdm.org/use#OriginalFile",
 *   uri = "http://pcdm.org/use#ServiceFile",
 *   type = "audio",
 *   default_action = "audio_generate_a_service_file_from_an_original_file",
 * )
 */
class ServiceFile extends TargetPluginBase {

}
