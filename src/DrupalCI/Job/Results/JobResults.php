<?php

/**
 * @file
 * Contains \DrupalCI\Job\Results\JobResults.
 */

namespace DrupalCI\Job\Results;

use DrupalCI\Plugin\JobTypes\JobInterface;

class JobResults {

  public function __construct(JobInterface $job) {
    $job->setJobResults($this);
  }

} 