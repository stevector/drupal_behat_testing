<?php
/**
* @file
* Contains \DrupalCI\Plugin\Preprocess\variable\TestItem.
*/

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;

/**
* @PluginID("testitem")
*/
class TestItem extends PluginBase {

  /**
  * {@inheritdoc}
  */
  public function target() {
    return 'DCI_TestGroups';
  }

  /**
  * {@inheritdoc}
  */
  public function process($testgroups, $testitem) {
    /**
     * $testitem format:
     *   $type:$name, where
     *     $type = [module|class|file]
     *     $name = Module/Class/File name
     *   Alternatively, passing 'all' will trigger all tests to run.
     *
     * Examples:
     *   Given:                                 Output:
     *   module:token                           --module token
     *   class:Drupal\book\Tests\BookTest       --class Drupal\book\Tests\BookTest
     *   file:core/modules/user/user.test       --file core/modules/user/user.test
     *   all                                    --all
     */

    if (empty($testitem)) {
      return $testgroups;
    }

    // Special case for 'all'
    if (strtolower($testitem) === 'all') {
      return "--all";
    }

    // Split the string components
    $components = explode(':', $testitem);
    if (!in_array($components[0], array('module', 'class', 'file', 'directory'))) {
      // Invalid entry.
      return $testgroups;
    }

    $testgroups = "--" . $components[0] . " " . $components[1];

    return $testgroups;
  }
}