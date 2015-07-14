<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\publish\GatherArtifacts
 *
 * Processes "publish: gather_artifacts:" instructions from within a job definition.
 * Generates job build artifact files in a common directory.
 */

namespace DrupalCI\Plugin\BuildSteps\publish;
use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("gather_artifacts")
 */
class GatherArtifacts extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $directory) {
    // Data format:  $directory: string containing the destination directory name
    // TODO: Validate $directory for security purposes

    // Create the destination directory if it doesn't already exist
    if (!is_dir($directory)) {
      Output::writeLn("<info>Creating build artifact directory: <options=bold>$directory</options=bold></info>");
      if (!mkdir($directory, 0777, TRUE)) {
        Output::error('', 'Error encountered while creating build artifact directory.  Unable to gather artifacts.');
        // TODO: Handle error / end processing
        return;
      }
    }

    // Retrieve the list of build artifacts from the job
    $artifacts = $job->getArtifacts();

    // Special cases: Job definition
    if (!empty($artifacts['jobDefinition'])) {
      $file = $directory . DIRECTORY_SEPARATOR . $artifacts['jobDefinition'];
      // TODO: Verify file name - unique, empty, etc.
      if (!file_put_contents($file, print_r($job->getDefinition(), TRUE))) {
        // TODO: Error encountered while writing out job definition
      }
    }

    // Iterate over the build artifacts
    foreach ($artifacts as $key => $artifact) {
      if (strtolower($artifact['type']) == 'file' || $artifact['type'] == 'directory') {
        // Copy artifact file to the build artifacts directory
        $file = $artifact['value'];
        $dest = $directory . DIRECTORY_SEPARATOR . $file;
        if (!copy($file, $dest)) {
          // TODO: Error Handling: File copy failed
        }
      }
      elseif (strtolower($artifact['type']) == 'string') {
        // Write string to new file with filename based on the string's key
        $dest = $directory . DIRECTORY_SEPARATOR . $key;
        if (!file_put_contents($dest, $artifact['value'])) {
          // TODO: Error Handling: File write failed
        }
      }
    }
  }
}