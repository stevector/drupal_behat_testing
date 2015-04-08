<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\generic\Drush
 *
 * Processes "[build_step]: drush:" instructions from within a job definition.
 */

namespace DrupalCI\Plugin\BuildSteps\generic;

use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;
use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("drush")
 */
class Drush extends ContainerCommand {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $command) {
    $cmd = "/.composer/vendor/bin/drush " . $command;
    parent::run($job, $cmd);
  }
}
