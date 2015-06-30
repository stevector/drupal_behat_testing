<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\publish\GatherArtifacts
 *
 * Processes "publish: zip_archive:" instructions from within a job definition.
 * Gathers the resulting job artifacts and generates a zip file.
 */

namespace DrupalCI\Plugin\BuildSteps\publish;
use DrupalCI\Console\Output;
use DrupalCI\Plugin\PluginBase;

/**
 * @PluginID("zip_archive")
 */
class GatherArtifacts extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    // $data defines the directory/filename of the desired zip file.
    $files = $job->getArtifactList();
    if (empty($files)) { return; }
    $zip = new \ZipArchive();
    $filename = $data;

    // Open the zip archive
    if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
      // TODO: Cannot open zip file ... throw an error.
      return;
    }

    // Add artifact files to zip file
    Output::writeLn("<info>Gathering artifact files:</info>");
    foreach ($files as $file) {
      if (file_exists($file)) {
        $zip->addFile($file);
        Output::writeLn("<comment>" . $file . "</comment>");
      }
      else {
        Output::writeln("<comment>" . $file . " NOT FOUND.</comment>");
      }
    }

    // Close the zip file
    $zip->close();

    // Store the artifacts filename in the job object for future reference
    $job->setArtifactFilename($data);

  }
}