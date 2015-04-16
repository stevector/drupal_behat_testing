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
    return 'DCI_RunScript';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_script, $source_value) {
    if (strtolower($source_value) === 'true') {
      $run_script .=  ' --verbose';
    }
    return $run_script;
  }

}
