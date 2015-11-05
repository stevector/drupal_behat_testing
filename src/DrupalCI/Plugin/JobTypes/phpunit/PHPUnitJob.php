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

class PHPUnitJob extends JobBase {

  /**
   * @var string
   */
  public $jobType = 'phpunit';

  /**
   * Default Arguments (defaultArguments)
   *
   * @var array
   *
   * Each DrupalCI Job type needs to contain a 'defaultArguments' property,
   * which contains a list of DCI_* variables and default values; which defines
   * the default behaviour of that job type if no additional overrides are
   * passed into an instance of that job type.
   */
  public $defaultArguments = array(
    'DCI_PHPVersion' => '5.4',
    'DCI_CoreRepository' => 'git://drupalcode.org/project/drupal.git',
    'DCI_CoreBranch' => '8.0.x',
    'DCI_GitCheckoutDepth' => '1',
    'DCI_RunScript' => "/var/www/html/core/vendor/phpunit/phpunit/phpunit",
    'DCI_RunOptions' => "bootstrap /var/www/html/core/tests/bootstrap.php",
    'DCI_RunTarget' => "/var/www/html/core/tests/Drupal/Tests"
  );

  /**
   * Required Arguments, which must be present in order for the job to attempt
   * to run.
   *
   * The expected format here is an array of key=>value pairs, where the key is
   * the name of a DCI_* environment variable, and the value is the array key
   * path from the parsed .yml file job definition that would need to be
   * traversed to get to the location that variable would exist in the job
   * definition.
   *
   * As an example, DCI_DBVersion defines the database type (mysql, pgsql, etc)
   * for a given job. In a parsed .yml job definition file, this information
   * would be stored in the value located at:
   * array(
   *   'environment' => array(
   *     'db' => VALUE
   *   )
   * );
   * Thus, thus the traversal path value stored in the 'requiredArguments'
   * array is the array keys 'environment:db'.
   *
   * As any required arguments for the phpunit job type are defined in the
   * 'defaultArguments' property, this array is empty.  However, that may not
   * always be the case for other job types.
   */
  public $requiredArguments = array(
    // 'DCI_PHPVersion' => 'environment:web',
    // 'DCI_RunScript' => 'execute:command',
  );

  /**
   * Return an array of possible argument variables for this job type.
   *
   * The 'availableArguments' property is intended to provide a complete list
   * of possible variable values which can affect this particular job type,
   * along with details regarding how each variable affects the job operation.
   * These are specified in an array, with the variable names used as the keys
   * for the array and the description used as the array values.
   */
  public $availableArguments = array(
    // ***** Variables Available for any job type *****
    'DCI_UseLocalCodebase' => 'Used to define a local codebase to be cloned (instead of performing a Git checkout)',
    'DCI_WorkingDir' => 'Defines the location to be used in creating the local copy of the codebase, to be mapped into the container as a container volume.  Default: /tmp/simpletest-[random string]',
    'DCI_ResultsServer' => 'Specifies the url string of a DrupalCI results server for which to publish job results',
    'DCI_ResultsServerConfig' => 'Specifies the location of a configuration file on the test runner containg a DrupalCI Results Server configuration to use in publishing results.',
    'DCI_JobBuildId' => 'Specifies a unique build ID assigned to this job from an upstream server',
    'DCI_JobId' => 'Specifies a unique results server node ID to use when publishing results for this job.',
    'DCI_JobType' => 'Specifies a default job type to assume for a "drupalci run" command',
    'DCI_EXCLUDE' => 'Specifies whether to exclude the .git directory during a clone of a local codebase.',  //TODO: Check logic, may be reversed.

    // ***** Default Variables defined for every phpUnit job *****
    'DCI_PHPVersion' => 'Defines the PHP Version used within the executable container for this job type.  Default: 5.4',
    'DCI_CoreRepository' => 'Defines the primary repository to be checked out while building the codebase to test.  Default: git://drupalcode.org/project/drupal.git',
    'DCI_CoreBranch' => 'Defines the branch on the primary repository to be checked out while building the codebase to test.  Default: 8.0.x',
    'DCI_GitCheckoutDepth' => 'Defines the depth parameter passed to git clone while checking out the core repository.  Default: 1',
    'DCI_RunScript' => 'Defines the default run script to be executed on the container.  Default: /var/www/html/core/scripts/run-tests.sh',
    'DCI_RunOptions' => 'A string containing initial runScript options to append to the run script when performing a job.',
    // Default: '--bootstrap /var/www/html/core/tests/bootstrap.php',
    'DCI_RunTarget' => 'A string defining the initial runScript target to append to the run script when performing a job.',
    // Default: '/var/www/html/core/tests/Drupal/Tests'

    // ***** Optional Arguments *****
    'DCI_Fetch' => 'Used to specify any files which should be downloaded while building out the codebase.',
    // Syntax: 'url1,relativelocaldirectory1;url2,relativelocaldirectory2;...'
    'DCI_Patch' => 'Defines any patches which should be applied while building out the codebase.',
    // Syntax: 'localfile1,applydirectory1;localfile2,applydirectory2;...'
    'DCI_RunScriptArguments' => 'An array of other build script options which will be added to the runScript command when executing a job.',
    // Syntax: 'argkey1,argvalue1;argkey2,argvalue2;argkey3;argkey4,argvalue4;
    'DCI_ListGroups' => 'Directs the test runner to list available test groups instead of executing tests.  (i.e. Specifies --list-groups).',
    'DCI_PHPUnitBootstrapFile',
  );
}
