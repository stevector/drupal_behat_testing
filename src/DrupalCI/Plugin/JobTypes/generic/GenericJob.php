<?php

/**
 * @file
 * Job class for 'Generic' jobs on DrupalCI.
 *
 * A generic job simply runs through and executes the job definition steps as
 * defined within the passed job definition file.
 */

namespace DrupalCI\Plugin\JobTypes\generic;

use DrupalCI\Plugin\JobTypes\JobBase;

/**
 * @PluginID("generic")
 */

class GenericJob extends JobBase {
  /**
   * Job Type (jobType)
   *
   * @var string
   *
   * This property is not referenced in the current code, but it is anticipated
   * that others may want to reference the job type from the object itself at
   * some point in the future.
   */
  public $jobtype = 'generic';

  /**
   * Overrides the getDefaultDefinitionTemplate() method from within JobBase.
   *
   * For 'generic' job types, if no file is provided, we assume the presence of
   * a drupalci.yml file in the current working directory.
   *
   * @param $job_type
   *   The name of the job type, used to select the appropriate subdirectory
   *
   * @return string
   *   The location of the default job definition template
   */
  public function getDefaultDefinitionTemplate($job_type) {
    return "./drupalci.yml";
  }

}