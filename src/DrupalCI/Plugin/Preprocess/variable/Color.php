<?php

/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\Color
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("color")
 */
class Color extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_RunScript';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_script, $source_value) {
    if (strtolower($source_value) === 'true') {
      $run_script .=  ' --color';
    }
    return $run_script;
  }

}
