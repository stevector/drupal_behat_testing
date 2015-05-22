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
}