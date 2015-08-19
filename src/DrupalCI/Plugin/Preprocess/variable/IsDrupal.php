<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\IsDrupal
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("is_drupal")
 */
class IsDrupal extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_DrupalVersion';
  }

  /**
   * {@inheritdoc}
   */
  public function process($drupal_version, $is_drupal) {
    if (!is_drupal) {
      return false;
    }
    return $drupal_version;
  }
}
