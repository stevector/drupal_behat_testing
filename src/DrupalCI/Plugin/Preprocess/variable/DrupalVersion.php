<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\DrupalVersion
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("drupalversion")
 */
class DrupalVersion extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return array('DCI_RunScript', 'DCI_RunOptions', 'DCI_SQLite');
  }

  /**
   * {@inheritdoc}
   */
  public function process($target_value, $drupal_version, $target) {
    switch (strtolower($target)) {
      case 'dci_runscript':
        // We can only adjust the DCI_RunScript value if the Drupal version
        // follows a core branch naming pattern. If a feature branch is passed
        // into DCI_CoreBranch then the user must also manually set
        // DCI_DrupalVersion accordingly.
        if ($this->isPreDrupal8($drupal_version)) {
          // If the run script is run-tests.sh, and drupal version is less than
          // D8, then we need to override the default script location.
          if ($target_value == "/var/www/html/core/scripts/run-tests.sh ") {
            // Update the run script value
            return "/var/www/html/scripts/run-tests.sh ";
          }
        }
        return $target_value;
      case 'dci_runoptions':
        if ($this->isPostDrupal8($drupal_version)) {
          $target_value .= " --keep-results ";
        }
        return $target_value;
      case 'dci_sqlite':
        // Drupal7 does not support the --sqlite or --dburl options on
        // run-tests.sh.
        if ($this->isPreDrupal8($drupal_version)) {
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

  protected function isPostDrupal8($core_branch) {
    if ($drupal_version = $this->getVersionFromBranch($core_branch)) {
      if (preg_match("/^(\d+)/", $drupal_version, $matches)) {
        if ($matches[1] > 7) {
          return true;
        }
      }
    }
    return false;
  }

}
