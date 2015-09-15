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
        $job->errorOutput("Error", "No valid patch file provided for the patch command.");
        return;
      }
      $workingdir = realpath($job->getWorkingDir());
      $patchfile = $details['patch_file'];
      $patchdir = (!empty($details['patch_dir'])) ? $details['patch_dir'] : $workingdir;
      // Validate target directory.
      if (!($directory = $this->validateDirectory($job, $patchdir))) {
        // Invalid checkout directory
        $job->errorOutput("Error", "The patch directory <info>$directory</info> is invalid.");
        return;
      }
      $cmd = "cd $directory && git apply -v -p1 $patchfile 2>&1 && cd -";

      exec($cmd, $cmdoutput, $result);
      if ($result !== 0) {
        // The command threw an error.
        $job->errorOutput("Patch failed", "The patch attempt returned an error.");
        Output::writeLn($cmdoutput);
        // TODO: Pass on the actual return value for the patch attempt
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
      }
      Output::writeLn("<comment>Patch <options=bold>$patchfile</options=bold> applied to directory <options=bold>$directory</options=bold></comment>");
    }
  }

}
