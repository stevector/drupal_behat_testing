<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\setup\Patch
 *
 * Processes "setup: patch:" instructions from within a job definition.
 */

namespace DrupalCI\Plugin\BuildSteps\setup;

use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("patch")
 */
class Patch extends SetupBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // Data format:
    // i) array('patch_file' => '...', 'patch_dir' => '...')
    // or
    // iii) array(array(...), array(...))
    // Normalize data to the third format, if necessary
    $data = (count($data) == count($data, COUNT_RECURSIVE)) ? [$data] : $data;
    Output::writeLn("<info>Entering setup_patch().</info>");
    foreach ($data as $key => $details) {
      if (empty($details['patch_file'])) {
        Output::error("Patch error", "No valid patch file provided for the patch command.");
        $job->error();
        return;
      }

      $working_dir = realpath($job->getJobCodebase()->getWorkingDir());
      $apply_dir = (!empty($details['patch_dir'])) ? $working_dir . DIRECTORY_SEPARATOR . $details['patch_dir'] : $working_dir;
      $patch_file = $details['patch_file'];

      // Validate patch file
      $real_file = realpath($working_dir . DIRECTORY_SEPARATOR . $details['patch_file']);
      if ($real_file === FALSE) {
        // Invalid patch file
        Output::error("Patch Error", "The patch file <info>${details['patch_file']}</info> is invalid.");
        $job->error();
        return;

      }

      // Validate target directory.
      if (!($directory = $this->validateDirectory($job, $apply_dir))) {
        // Invalid checkout directory
        Output::error("Patch Error", "The patch directory <info>$directory</info> is invalid.");
        $job->error();
        return;
      }

      //$cmd = "cd $directory && git apply -v -p1 $real_file && cd -";
      $cmd = "git apply -v -p1 $real_file --directory $directory 2>&1";

      $this->exec($cmd, $cmdoutput, $result);
      if ($result !== 0) {
        // The command threw an error.
        Output::writeLn($cmdoutput);
        Output::error("Patch Error", "The patch attempt returned an error.  Error code: $result");
        $job->error();
        // TODO: Pass on the actual return value for the patch attempt
        return;
      }
      Output::writeLn("<comment>Patch <options=bold>$patch_file</options=bold> applied to directory <options=bold>$directory</options=bold></comment>");

      // Log modified files to the Codebase object
      $job_codebase = $job->getJobCodebase();

      foreach ($cmdoutput as $line) {
        Output::writeln($line);
        $parse = explode("Applied patch ", $line);
        if (!empty($parse[1])) {
          $parse = explode(" cleanly.", $parse[1]);
          $job_codebase->addModifiedFile($parse[0]);
        }
      }
    }
  }
}
