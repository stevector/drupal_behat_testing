<?php

/**
 * @file
 * Contains \DrupalCI\Job\CodeBase\JobCodebase
 */

namespace DrupalCI\Job\CodeBase;

use DrupalCI\Console\Output;
use DrupalCI\Job\CodeBase\Repository;
use DrupalCI\Job\CodeBase\LocalRepository;
use DrupalCI\Job\Definition\JobDefinition;
use DrupalCI\Plugin\JobTypes\JobInterface;

class JobCodebase {

  protected $working_dir;

  protected $core_project;

  protected $core_version;

  protected $repositories;

  public function __construct(JobInterface $job) {
    $job->setJobCodebase($this);
  }

  /**
   * Initialize Codebase
   */
  public function setupWorkingDirectory(JobDefinition $job_definition) {
    // Check if the target working directory has been specified.
    $working_dir = $job_definition->getDCIVariable('DCI_WorkingDir');
    $tmp_directory = sys_get_temp_dir();

    // Generate a default directory name if none specified
    if (empty($working_dir)) {
      // Case:  No explicit working directory defined.
      $working_dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $job_definition->getDCIVariable('DCI_JobBuildId');
    }
    else {
      // We force the working directory to always be under the system temp dir.
      if (strpos($working_dir, realpath($tmp_directory)) !== 0) {
        if (substr($working_dir, 0, 1) == DIRECTORY_SEPARATOR) {
          $working_dir = $tmp_directory . $working_dir;
        }
        else {
          $working_dir = $tmp_directory . DIRECTORY_SEPARATOR . $working_dir;
        }
      }
    }
    // Create directory if it doesn't already exist
    if (!is_dir($working_dir)) {
      $result = mkdir($working_dir, 0777, TRUE);
      if (!$result) {
        // Error creating checkout directory
        Output::error('Directory Creation Error', 'Error encountered while attempting to create local working directory');
        return FALSE;
      }
      Output::writeLn("<info>Checkout directory created at <options=bold>$working_dir</options=bold></info>");
    }

    // Validate that the working directory is empty.  If the directory contains
    // an existing git repository, for example, our checkout attempts will fail
    // TODO: Prompt the user to ask if they'd like to overwrite
    $iterator = new \FilesystemIterator($working_dir);
    if ($iterator->valid()) {
      // Existing files found in directory.
      Output::error('Directory not empty', 'Unable to use a non-empty working directory.');
      return FALSE;
    };

    // Convert to the full path and ensure our directory is still valid
    $working_dir = realpath($working_dir);
    if (!$working_dir) {
      // Directory not found after conversion to canonicalized absolute path
      Output::error('Directory not found', 'Unable to determine working directory absolute path.');
      return FALSE;
    }

    // Ensure we're still within the system temp directory
    if (strpos(realpath($working_dir), realpath($tmp_directory)) !== 0) {
      Output::error('Directory error', 'Detected attempt to traverse out of the system temp directory.');
      return FALSE;
    }

    // If we arrive here, we have a valid empty working directory.
    $this->setWorkingDir($working_dir);
    $job_definition->setDCIVariable('DCI_WorkingDir', $working_dir);
  }

  /**
   * @param mixed $core_project
   */
  public function setCoreProject($core_project)
  {
    $this->core_project = $core_project;
  }

  /**
   * @return mixed
   */
  public function getCoreProject()
  {
    return $this->core_project;
  }

  /**
   * @param mixed $core_version
   */
  public function setCoreVersion($core_version)
  {
    $this->core_version = $core_version;
  }

  /**
   * @return mixed
   */
  public function getCoreVersion()
  {
    return $this->core_version;
  }

  /**
   * @param mixed $repositories
   */
  public function setRepositories($repositories)
  {
    $this->repositories = $repositories;
  }

  /**
   * @return mixed
   */
  public function getRepositories()
  {
    return $this->repositories;
  }

  /**
   * @param mixed $working_dir
   */
  public function setWorkingDir($working_dir)
  {
    $this->working_dir = $working_dir;
  }

  /**
   * @return mixed
   */
  public function getWorkingDir()
  {
    return $this->working_dir;
  }






}