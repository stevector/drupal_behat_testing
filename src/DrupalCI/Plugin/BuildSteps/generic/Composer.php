<?php

/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\generic\Composer
 */

namespace DrupalCI\Plugin\BuildSteps\generic;

use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("composer")
 *
 * Processes "[build_step]: composer:" instructions from within a job
 * definition.
 */
class Composer extends ContainerCommand {

  /**
   * {@inheritdoc}
   *
   * @param string|array $arguments
   *   Arguments for a composer command. May be a string if one composer command
   *   is required to run or an array if multiple commands should run.
   */
  public function run(JobInterface $job, $arguments) {
    // Normalize the arguments to an array format.
    $arguments_list = (array) $arguments;

    foreach ($arguments_list as $arguments) {
      $cmd = $this->buildComposerCommand($arguments);
      parent::run($job, $cmd);
    }
  }

  /**
   * Returns a full composer command based on the passed-in arguments.
   *
   * @param string $arguments
   *   The arguments for the composer command.
   *
   * @return string
   *   The full composer command string.
   */
  protected function buildComposerCommand($arguments) {
    return "composer $arguments";
  }

}
