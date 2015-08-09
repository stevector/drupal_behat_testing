<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\generic\Drush
 */

namespace DrupalCI\Plugin\BuildSteps\generic;

use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("drush")
 *
 * Processes "[build_step]: drush:" instructions from within a job definition.
 */
class Drush extends ContainerCommand {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // Data format: 'drush arguments' or array('drush arguments', 'drush arguments')
    // $data May be a string if one version required, or array if multiple
    // Normalize data to the array format, if necessary
    $data = is_array($data) ? $data : [$data];

    foreach ($data as $command) {
      $cmd = "/.composer/vendor/bin/drush " . $command;
      parent::run($job, $cmd);
    }
  }
}
