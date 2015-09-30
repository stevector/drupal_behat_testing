<?php

/**
 * @file
 * Job class for SimpleTest jobs on DrupalCI.
 */

namespace DrupalCI\Plugin\JobTypes\simpletest;

use DrupalCI\Plugin\JobTypes\JobBase;

/**
 * @PluginID("simpletest")
 */

class SimpletestJob extends JobBase {

  /**
   * @var string
  */
  public $jobType = 'simpletest';

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
    'DCI_DBVersion' => 'mysql-5.5',
    'DCI_PHPVersion' => '5.5',
    'DCI_CoreRepository' => 'git://drupalcode.org/project/drupal.git',
    'DCI_CoreBranch' => '8.0.x',
    'DCI_GitCheckoutDepth' => '1',
    'DCI_RunScript' => '/var/www/html/core/scripts/run-tests.sh ',
    'DCI_DBUser' => 'drupaltestbot',
    'DCI_DBPassword' => 'drupaltestbotpw',
    'DCI_DBUrl' => 'dbtype://host', // DBVersion, DBUser and DBPassword variable plugins will change this.
    'DCI_TestGroups' => '--all',
    'DCI_SQLite' => '/var/www/html/results/simpletest.sqlite',
    'DCI_Concurrency' => 4,
    'DCI_GitCommitHash' => '',
    // 'DCI_XMLOutput' => '/var/www/html/results/xml',
    'DCI_PHPInterpreter' => '/opt/phpenv/shims/php',
    'DCI_RunOptions' => 'color;keep-results',
    'DCI_SyntaxCheck' => TRUE,
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
   * As any required arguments for the simpletest job type are defined in the
   * 'defaultArguments' property, this array is empty.  However, that may not
   * always be the case for other job types.
   */
  public $requiredArguments = array(
    // 'DCI_DBVersion' => 'environment:db',
    // 'DCI_PHPVersion' => 'environment:web',
    // 'DCI_RunScript' => 'execute:command',
    // 'DCI_TestGroups' => 'execute:command'
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

     // ***** Default Variables defined for every simpletest job *****
    'DCI_DBVersion' => 'Defines the database version for this particular simpletest run. May map to a required service container. Default: mysql-5.5',
    'DCI_PHPVersion' => 'Defines the PHP Version used within the executable container for this job type.  Default: 5.5',
    'DCI_CoreRepository' => 'Defines the primary repository to be checked out while building the codebase to test.  Default: git://drupalcode.org/project/drupal.git',
    'DCI_CoreBranch' => 'Defines the branch on the primary repository to be checked out while building the codebase to test.  Default: 8.0.x',
    'DCI_GitCheckoutDepth' => 'Defines the depth parameter passed to git clone while checking out the core repository.  Default: 1',
    'DCI_RunScript' => 'Defines the default run script to be executed on the container.  Default: /var/www/html/core/scripts/run-tests.sh',
    'DCI_RunOptions' => 'A string containing a series of any other run script options to append to the run script when performing a job.',
    'DCI_DBUser' => 'Defines the default database user to be used on the site database.  Default: drupaltestbot',
    'DCI_DBPassword' => 'Defines the default database password to be used on the site database.  Default: drupaltestbotpw',
    'DCI_DBUrl' => 'Define the --dburl parameter to be passed to the run script.  Default: dbtype://host (DBVersion, DBUser and DBPassword variable plugins will populate this).',
    'DCI_TestGroups' => 'Defines the target test groups to run.  Default: --all',
    'DCI_SQLite' => 'Defines the location of the sqlite database used to store test results.  Default: /var/www/html/results/simpletest.sqlite',
    'DCI_Concurrency' => 'Defines the value to pass to the --concurrency argument of the run script.  Default: 4',
    'DCI_XMLOutput' => 'Defines the directory where xml results will be stored.  Default: output/var/www/html/results/xml',
    'DCI_PHPInterpreter' => 'Defines the php interpreter to be passed to the Run Script in the --php argument.  Default: /opt/phpenv/shims/php',
    // Default: 'color;'

    // ***** Optional arguments *****
    'DCI_DieOnFail' => 'Defines whether to include the --die-on-fail flag in the Run Script options',
    'DCI_SQLite' => 'Defines whether to include the --sqlite flag in the Run Script options',
    'DCI_Fetch' => 'Used to specify any files which should be downloaded while building out the codebase.',
    // Syntax: 'url1,relativelocaldirectory1;url2,relativelocaldirectory2;...'
    'DCI_Patch' => 'Defines any patches which should be applied while building out the codebase.',
    // Syntax: 'localfile1,applydirectory1;localfile2,applydirectory2;...'
    'DCI_ResultsDirectory' => 'Defines the local directory within the container where the xml results file should be written.',
    'DCI_RunScriptArguments' => 'An array of other build script options which will be added to the runScript command when executing a job.',
    // Syntax: 'argkey1,argvalue1;argkey2,argvalue2;argkey3;argkey4,argvalue4;'
    'DCI_GitCommitHash' => 'This allows to checkout specific core commits, useful for regression testing',
  );

  /**
   * Identify any priority variables which must be pre-processed before others
   *
   * Variables provided in this array will be prioritized and run first, before
   * any other DCI_* variable preprocessors are executed.
   */
  public $priorityArguments = array(
    // CoreProject doesn't *need* to run first, but it seems like a good idea
    'DCI_CoreProject',
    // Expand run options to their argument format, before adding arguments
    'DCI_RunOptions',
    // CoreBranch needs to be able to override the SQLite variable before the
    // SQLite option is added to RunOptions
    'DCI_CoreBranch',
  );

  /**
   * Provide the details for job-specific build artifacts.
   *
   * This should be overridden by job-specific classes, to define the build
   * artifacts which should be collected for that class.
   *
   * For Simpletest jobs, this includes:
   *   - the xml output from run-tests.sh (if present)
   *   - sqlite database (if present)
   *   - php error log
   *   - apache access log
   *   - apache error log
   *   - any verbose output from run-tests.sh (if present)
   *
   * Syntax:
   *   phpunit results file at ./results.txt:  array('phpunit_results', './results.txt'),
   *   multiple xml files within results/xml directory: array('xml_results', 'results/xml', 'directory'),
   *   a string representing red/blue outcome: array('color', 'red', 'string')
   *   etc.
   */
  protected $buildArtifacts = array(
    array('testgroups', '/var/www/html/artifacts/testgroups.txt'),
    // array('run_tests_xml', '/var/www/html/results/xml', 'directory'),
    array('sqlite_test_db', '/var/www/html/results/simpletest.sqlite'),
    // array('php_error_log', 'TODO: Locate'),
    // array('apache_access_log', 'TODO: Locate'),
    // array('apache_error_log', 'TODO: Locate'),
    // array('run_tests_verbose', 'TODO: Locate', 'directory'),
  );

}
