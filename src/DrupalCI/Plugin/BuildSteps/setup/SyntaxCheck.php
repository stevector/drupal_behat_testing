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
        $file_path = $workingdir . "/" . $file;
        // Checking for: if in a vendor dir, if the file still exists, or if the first 32 (length - 1) bytes of the file contain <?php
        if ((strpos( $file, '/vendor/') === FALSE) && file_exists($file_path) && (strpos(fgets(fopen($file_path, 'r'), 33), '<?php') !== FALSE)) {
          $bash_array .= "$file\n";
        }
      }
      $lintable_files = 'artifacts/lintable_files.txt';
      Output::writeLn("<info>" . $workingdir . "/" . $lintable_files . "</info>");
      file_put_contents($workingdir . "/" . $lintable_files, $bash_array);
      // Make sure
      if (0 < filesize($workingdir . "/" . $lintable_files)) {
        // TODO: Remove hardcoded /var/www/html.
        // This should be come JobCodeBase->getLocalDir() or similar
        // Use xargs to concurrently run linting on file.
        $cmd = "cd /var/www/html && xargs -P $jobconcurrency -a $lintable_files -I {} php -l '{}'";
        $command = new ContainerCommand();
        $command->run($job, $cmd);
      }
    }
  }
}
