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
use DrupalCI\Job\CodeBase\Patch as PatchFile;

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
    $codebase = $job->getJobCodebase();
    foreach ($data as $key => $details) {
      if (empty($details['patch_file'])) {
        Output::error("Patch error", "No valid patch file provided for the patch command.");
        $job->error();
        return;
      }
      // Create a new patch object
      $patch = new PatchFile($details, $codebase);
      // Validate our patch's source file and target directory
      if (!$patch->validate()) {
        $job->error();
        return;
      }
      // Apply the patch
      if (!$patch->apply()) {
        $job->error();
        return;
      };
      // Update our list of modified files
      $codebase->addModifiedFiles($patch->getModifiedFiles());
    }
  }
}
