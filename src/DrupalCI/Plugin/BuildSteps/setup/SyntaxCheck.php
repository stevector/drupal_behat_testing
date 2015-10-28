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

      $workingdir = $codebase->getWorkingDir();
      $jobconcurrency = $job->getJobDefinition()->getDCIVariable('DCI_Concurrency');
      $bash_array = "";
      foreach ($modified_files as $file) {
        if (!strpos( $file, "vendor")) {
          $bash_array .= "$file\n";
        }
      }
      file_put_contents($workingdir . "/artifacts/modified_files.txt", $bash_array);
      // TODO: Remove hardcoded /var/www/html.
      // This should be come JobCodeBase->getLocalDir() or similar
      $cmd = "cd /var/www/html && xargs -P $jobconcurrency -a artifacts/modified_files.txt -I {} -i bash -c '[[ -e '{}' ]] && [[ \$(head -n 1 '{}') = *php* ]] && php -l '{}''";
      $command = new ContainerCommand();
      $command->run($job, $cmd);
    }
  }
}
