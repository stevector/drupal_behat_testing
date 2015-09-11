<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\dbcreate\MySQL.
 */

namespace DrupalCI\Plugin\BuildSteps\dbcreate;

use DrupalCI\Plugin\BuildSteps\generic\ContainerCommand;
use DrupalCI\Plugin\JobTypes\JobInterface;

/**
 * @PluginID("mysql")
 */
class MySQL extends ContainerCommand {

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $data) {
    $parts = parse_url($job->getBuildVar('DCI_DBUrl'));
    $host = $parts['host'];
    $user = $parts['user'];
    $pass = $parts['pass'];
    $db_name = $data ?: ltrim($parts['path'], '/');
    $cmd = "mysql -h $host -u $user -p$pass -e 'CREATE DATABASE $db_name'";
    parent::run($job, $cmd);
  }
}
