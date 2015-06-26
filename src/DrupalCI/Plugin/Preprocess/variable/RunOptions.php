<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\RunOptions
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("runoptions")
 */
class RunOptions extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_RunOptions';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_options, $arguments) {
    // Input format: (string) $value = "argument1,value1;argument2,value2;argument3;argument4,value4;..."
    foreach (explode(';', $arguments) as $argument_string) {
      if (strpos($argument_string, ',') === FALSE) {
        $run_options .= " --" . $argument_string;
      }
      else {
        list($argument_name, $argument_value) = explode(',', $argument_string);
        $run_options .= " --" . $argument_name . " " . $argument_value;
      }
    }
    return "$run_options";
  }

}
