<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\CoreBranch
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("corebranch")
 */
class CoreBranch extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return array('DCI_DrupalVersion');
  }

  /**
   * {@inheritdoc}
   */
  public function process($target_value, $core_branch, $target) {
    switch ($target) {
      case 'DCI_DrupalVersion':
        // If not a Drupal test, then is_drupal may set DrupalVersion=false.
        // If this is already happened, we don't want to override it; but if
        // it has not, anything we do here will be overridden when is_drupal
        // runs.
        if (!empty($target_value)) {
          // Check whether our version matches the X.Y or X.Y.Z patterns.  If
          // not, we can assume that it's been manually set, in which case we
          // don't need to over-ride it.
          if ($drupal_version = $this->getVersionFromBranch($core_branch)) {
            return $drupal_version;
          }
        }
        return $target_value;
      case 'DCI_RunScript':
        // We can only adjust the DCI_RunScript value if we can determine the
        // Drupal version from the provided core branch. If not, then we assume
        // that the core branch has been manually set, in which case the user
        // must also manually set DCI_DrupalVersion accordingly.
        if ($this->isPreDrupal8($core_branch)) {
          // If the run script is run-tests.sh, and drupal version is less than
          // D8, then we need to override the default script location.
          if ($target_value == "/var/www/html/core/scripts/run-tests.sh ") {
            // Update the run script value
            return "/var/www/html/scripts/run-tests.sh ";
          }
        }
        return $target_value;
      case 'DCI_SQLite':
        // Drupal7 does not support the --sqlite option on run-tests.sh
        if ($this->isPreDrupal8($core_branch)) {
          return "";
        }
        return $target_value;
    }
  }

  protected function getVersionFromBranch($core_branch) {
    $pattern = "/^(\d+\.(\d+\.)?(\d+|x))(-(alpha|beta|rc)\d+)?$/";
    if (preg_match($pattern, $core_branch, $matches)) {
      // First match will be the full x.y.z (or x.y) version
      return $matches[1];
    }
    return false;
  }

  protected function isPreDrupal8($core_branch) {
    if ($drupal_version = $this->getVersionFromBranch($core_branch)) {
      if (preg_match("/^(\d+)/", $drupal_version, $matches)) {
        if ($matches[1] < 8) {
          return true;
        }
      }
    }
    return false;
  }
}
