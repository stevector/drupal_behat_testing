<?php

/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\dbinstall\PostgreSQL.
 */

namespace DrupalCI\Plugin\BuildSteps\dbcreate;

use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;
use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("pgsql")
 */
class PostgreSQL extends ContainerCommand {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {

    $parts = parse_url($job->getBuildvar('DCI_DBURL'));
    $host = $parts['host'];
    $user = $parts['user'];
    $pass = $parts['pass'];
    $db_name = $data ?: ltrim($parts['path'], '/');

    // Create role, database, and schema for PostgreSQL commands.
    $createdb = "PGPASSWORD=$pass PGUSER=$user createdb -E 'UTF-8' -O $user -h $host -U $user $db_name";

    parent::run($job, $createdb);
  }
}
?>
