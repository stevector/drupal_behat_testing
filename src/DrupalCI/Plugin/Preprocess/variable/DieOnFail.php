<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\DieOnFail.
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("dieonfail")
 */
class DieOnFail extends PluginBase {

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
      $run_options .=  ' --die-on-fail';
    }
    return $run_options;
  }

}
