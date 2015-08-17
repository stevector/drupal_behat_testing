<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\SQLite.
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("sqlite")
 */
class SQLite extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_RunOptions';
  }

  /**
   * {@inheritdoc}
   */
  public function process($run_options, $sqlite) {
    if (!empty($sqlite)) {
      return "$run_options --sqlite $sqlite";
    } else {
      return "$run_options";
    }
  }

}
