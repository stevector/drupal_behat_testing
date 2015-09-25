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

        // Hack to create a xml file for processing by Jenkins.
        // TODO: Remove once proper job failure processing is in place

        // Save an xmlfile to the jenkins artifact directory.
        // find jenkins artifact dir
        //
        $source_dir = $job->getBuildVar('DCI_CheckoutDir');
        // TODO: Temporary hack.  Strip /checkout off the directory
        $artifact_dir = preg_replace('#/checkout$#', '', $source_dir);

        // Set up output directory (inside working directory)
        $output_directory = $artifact_dir . DIRECTORY_SEPARATOR . 'artifacts' . DIRECTORY_SEPARATOR . $job->getBuildVar('DCI_JunitXml');

        mkdir($output_directory, 0777, TRUE);
        $output = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '�', implode("\n", $cmdoutput));

        $xml_error = '<?xml version="1.0"?>

                      <testsuite errors="1" failures="0" name="Error: Patch failed to apply" tests="1">
                        <testcase classname="Apply Patch" name="' . $patchfile . '">
                          <error message="Patch Failed to apply" type="PatchFailure">Patch failed to apply</error>
                        </testcase>
                        <system-out><![CDATA[' . $output . ']]></system-out>
                      </testsuite>';
        file_put_contents($output_directory . "/patchfailure.xml", $xml_error);

        return;
      };
      // Update our list of modified files
      $codebase->addModifiedFiles($patch->getModifiedFiles());
    }
  }
}
