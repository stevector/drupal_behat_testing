<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\generic\Command
 *
 * Processes "[build_step]: command:" instructions from within a job definition.
 */

namespace DrupalCI\Plugin\BuildSteps\generic;

use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
/**
 * @PluginID("testcommand")
 */
class ContainerTestingCommand extends ContainerCommand {

  /**
   * {@inheritdoc}
   */

  /*
   * Overrides ContainerCommands check with a specific signal check.
   */
  protected function checkCommandStatus($signal) {
    if ($signal > 1) {
      Output::error('Error', "Received a failed return code from the last command executed on the container.  (Return status: " . $signal . ")");
      return 1;
    }
    else {
      return 0;
    }
  }
}
