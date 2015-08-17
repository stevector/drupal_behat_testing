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
    return 'DCI_Runptions';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_options, $source_value) {
    if (strtolower($source_value) === 'true') {
      $run_options .=  ' --color';
    }
    return $run_options;
  }

}
