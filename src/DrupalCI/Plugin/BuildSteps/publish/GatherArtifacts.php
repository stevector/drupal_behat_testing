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
use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;

/**
 * @PluginID("gather_artifacts")
 */
class GatherArtifacts extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $target_directory) {

    $docker = $job->getDocker();
    $manager = $docker->getContainerManager();

    Output::writeLn("<comment>Gathering job build artifacts in a common directory ...</comment>");

    // Create the destination directory
    if (!empty($target_directory)) {
      $command = new ContainerCommand();
      $command->run($job, "mkdir -p $target_directory");
    }

    // Store the directory in our job object
    $job->setArtifactDirectory($target_directory);

    // Retrieve the list of build artifacts from the job
    $artifacts = $job->getArtifacts();

    // Iterate over the build artifacts
    foreach ($artifacts->getArtifacts() as $key => $artifact) {
      if ($key == 'jobDefinition') {
        $destination_filename = $artifact->getValue();

        // Retrieve the job definition from the job
        $definition = $job->getJobDefinition()->getDefinition();
        // write the job definition out to a file in the artifact directory on the container.
        if (!empty($destination_filename)) {
          $file = $target_directory . DIRECTORY_SEPARATOR . $destination_filename;
          // TODO: Verify file name - unique, empty, etc.
          $command = new ContainerCommand();
          $cmd = "cat >$file <<EOL \n" . print_r($definition, TRUE) . "\nEOL";
          $command->run($job, $cmd);
        }
        else {
          // TODO: Exception handling
          Output::writeLn('<info>Error generating job definition build artifact.');
        }
      }
      elseif (strtolower($artifact->getType()) == 'file' || $artifact->getType() == 'directory') {
        // Copy artifact file to the build artifacts directory
        $file = $artifact->getValue();
        $dest = $target_directory . DIRECTORY_SEPARATOR . basename($file);
        if ($file !== $dest) {
          $command = new ContainerCommand();
          $cmd = "cp -r $file $dest";
          $command->run($job, $cmd);
        }
        else {
          Output::writeLn("<info>Skipping $file, as it already exists in the build artifact directory.");
        }
      }
      elseif (strtolower($artifact->getType) == 'string') {
        // Write string to new file with filename based on the string's key
        $dest = $target_directory . DIRECTORY_SEPARATOR . $key;
        $content = $artifact->getValue;
        $command = new ContainerCommand();
        $cmd = "cat >$dest <<EOL \n" . print_r($content, TRUE) . "\nEOL";
        $command->run($job, $cmd);
      }
    }
  }
}