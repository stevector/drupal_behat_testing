<?php

/**
 * @file
 * Job class for PHPUnit jobs on DrupalCI.
 */

namespace DrupalCI\Plugin\JobTypes\phpunit;

use DrupalCI\Plugin\JobTypes\JobBase;

/**
 * @PluginID("phpunit")
 */
  // ^^^ Use an annotation to define the job type name.

class PHPUnitJob extends JobBase {
  // ^^^ Extend JobBase, to get the main test runner functionality

  /**
   * @var string
   */
  public $jobtype = 'phpunit';
  // I don't believe this property is currently used; but anticipate we will
  // want to reference the jobtype from the object itself at some point.


  // ****************** Start Validation related properties ******************
  /**
   * Required Arguments, which must be present in order for the job to attempt
   * to run.
   *
   * The expected format here is an array of key=>value pairs, where the key is
   * the name of a DCI_* environment variable, and the value is the array key
   * path from the parsed .yml file job definition that would need to be
   * traversed to get to the location that variable would exist in the job
   * definition.
   */
  public $requiredArguments = array(
  );

  public $availableArguments = array(
    'DCI_PHPVersion',
    'DCI_UseLocalCodebase',
    'DCI_ListGroups',
    'DCI_PHPUnitBootstrapFile',
    'DCI_TESTGROUPS'

  );

  // ******************* End Validation related properties *******************


  // **************** Start job definition related properties ****************

  public $defaultArguments = array(
    'DCI_PHPVersion' => '5.4',
    'DCI_CoreRepository' => 'git://drupalcode.org/project/drupal.git',
    'DCI_CoreBranch' => '8.0.x',
    'DCI_GitCheckoutDepth' => '1',
    'DCI_RunScript' => "/var/www/html/core/vendor/phpunit/phpunit/phpunit --bootstrap=/var/www/html/core/tests/bootstrap.php /var/www/html/core/tests/Drupal/Tests",
  );
}
