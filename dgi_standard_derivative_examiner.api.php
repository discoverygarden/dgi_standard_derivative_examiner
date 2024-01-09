<?php

/**
 * @file
 * API/hook definitions.
 */

/**
 * Allow alter of model plugin info.
 *
 * @param array $info
 *   The plugin info to be altered.
 */
function hook_dgi_standard_derivative_examiner_model_plugin_info_alter(array &$info) : void {
}

/**
 * Allow alter of target plugin info to targets of the given MODEL.
 *
 * @param array $info
 *   The plugin info to be altered.
 */
function hook_dgi_standard_derivative_examiner_MODEL_target_plugin_info_alter(array &$info) : void {
}
