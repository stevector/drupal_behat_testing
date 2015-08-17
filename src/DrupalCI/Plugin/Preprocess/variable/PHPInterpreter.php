<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\PHPInterpreter.
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("phpinterpreter")
 */
class PHPInterpreter extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_RunOptions';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_options, $php_path) {
    if (!empty($php_path)) {
      return "$run_options --php $php_path";
    }
    return $run_options;
  }
}
