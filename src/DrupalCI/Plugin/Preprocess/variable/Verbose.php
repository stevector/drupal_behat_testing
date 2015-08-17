<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\Verbose
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("verbose")
 */
class Verbose extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_RunOptions';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_options, $source_value) {
    if (strtolower($source_value) === 'true') {
      $run_options .=  ' --verbose';
    }
    return $run_options;
  }

}
