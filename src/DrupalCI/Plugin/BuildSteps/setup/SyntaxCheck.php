<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\setup\SyntaxCheck
 *
 * Processes "setup: syntaxcheck:" instructions from within a job definition.
 */

namespace DrupalCI\Plugin\BuildSteps\setup;

use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;

/**
 * @PluginID("syntaxcheck")
 */
class SyntaxCheck extends SetupBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    if ($data != FALSE) {
      Output::writeLn('<info>SyntaxCheck checking for php syntax errors.</info>');

      $codebase = $job->getJobCodebase();
      $modified_files = $codebase->getModifiedFiles();
      if (empty($modified_files)) {
        return;
      }

      $bash_array = "";
      foreach ($modified_files as $file) {
        $bash_array .= "$file ";
      }

      // TODO: Remove hardcoded /var/www/html.
      // This should be come JobCodeBase->getLocalDir() or similar
      $cmd = "cd /var/www/html && i=0; for file in $bash_array; do php -l \$file; ((i+=\$?)); done; return i;";
      $command = new ContainerCommand();
      $command->run($job, $cmd);
    }
  }
}
