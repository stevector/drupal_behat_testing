<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\BuildSteps\publish\JunitXmlFormat
 *
 * Processes "publish: junit_xmlformat:" instructions from within a job
 * definition.  Connects to the database, queries for the tests, and reformats
 * them in a sane manner.  (If there is a sqlite results database!)
 */

namespace DrupalCI\Plugin\BuildSteps\publish;
use Docker\Docker;
use DrupalCI\Console\Output;
use DrupalCI\Plugin\JobTypes\JobInterface;
use DrupalCI\Plugin\PluginBase;
use PDO;
use DOMDocument;

/**
 * @PluginID("junit_xmlformat")
 */
class JunitXMLFormat extends PluginBase {

  protected $testlist = [];
  public function setTestlist($testlist)  {  $this->testlist = $testlist; }
  public function getTestlist() {  return $this->testlist; }

  protected function loadTestList($file) {
    $test_list = file($file, FILE_IGNORE_NEW_LINES);
    // Get rid of the first four lines
    $this->setTestlist(array_slice($test_list, 4));
  }

  /**
   * {@inheritdoc}
   */
  public function run(JobInterface $job, $output_directory) {
    // Set up initial variable to store tests
    $CoreBranch = $job->getBuildVars()["DCI_CoreBranch"];
    $DBUrlArray = parse_url($job->getBuildVars()["DCI_DBUrl"]);
    $DBVersion = $job->getBuildVars()["DCI_DBVersion"];
    $DBScheme = $DBUrlArray["scheme"];
    $DBUser   = (!empty($DBUrlArray["user"])) ? $DBUrlArray["user"] : "";
    $DBPass   = (!empty($DBUrlArray["pass"])) ? $DBUrlArray["pass"] : "";
    $DBDatabase = str_replace('/','',$DBUrlArray["path"]);
    $DBIp = $job->getServiceContainers()["db"][$DBVersion]["ip"];
    $tests = [];

    // Load the list of tests from the testgroups.txt build artifact
    // Assumes that gatherArtifacts plugin has run.
    // TODO: Verify that gatherArtifacts has ran.
    $source_dir = $job->getJobCodebase()->getWorkingDir();
    // TODO: Temporary hack.  Strip /checkout off the directory
    $artifact_dir = preg_replace('#/checkout$#', '', $source_dir);
    $this->loadTestList($source_dir . DIRECTORY_SEPARATOR . 'artifacts/testgroups.txt');

    // Set up output directory (inside working directory)
    $output_directory = $artifact_dir . DIRECTORY_SEPARATOR . 'artifacts' . DIRECTORY_SEPARATOR . $output_directory;
    mkdir($output_directory, 0777, TRUE);

    // Set an initial default group, in case leading tests are found with no group.
    $group = 'nogroup';
    // Iterate through and process the test list
    $test_list = $this->getTestlist();
    if(strcmp($CoreBranch,'7.x') === 0 || strcmp($CoreBranch,'6.x') === 0){

      foreach ($test_list as $output_line) {
        if (substr($output_line, 0, 3) == ' - ') {
          // This is a class
          $class = str_replace(array('(',')'),'',end(explode(' ', $output_line)));
          $test_groups[$class] = $group;
        }
        else {
          // This is a group
          $group = ucwords($output_line);
        }
      }
      $PDO_con = "$DBScheme:host=$DBIp;dbname=$DBDatabase";
      $db = new PDO( $PDO_con, $DBUser, $DBPass);

    } else {

      foreach ($test_list as $output_line) {
        if (substr($output_line, 0, 3) == ' - ') {
          // This is a class
          $class = substr($output_line, 3);
          $test_groups[$class] = $group;
        }
        else {
          // This is a group
          $group = ucwords($output_line);
        }
      }
      // Crack open the sqlite database.
      $dbfile = $source_dir . DIRECTORY_SEPARATOR . 'artifacts' . DIRECTORY_SEPARATOR . basename($job->getBuildVar('DCI_SQLite'));
      $db = new PDO('sqlite:' . $dbfile);
    }

    // query for simpletest results
    $results_map = array(
      'pass' => 'Pass',
      'fail' => 'Fail',
      'exception' => 'Exception',
      'debug' => 'Debug',
    );

    $q_result = $db->query('SELECT * FROM simpletest ORDER BY test_id, test_class, message_id;');

    $results = array();

    $cases = 0;
    $errors = 0;
    $failures = 0;

    //while ($result = $q_result->fetchAll()) {
    while ($result = $q_result->fetch(PDO::FETCH_ASSOC)) {
      if (isset($results_map[$result['status']])) {
        // Set the group from the lookup table
        $test_group = $test_groups[$result['test_class']];

        // Set the test class
        if (isset($result['test_class'])) {
          $test_class = $result['test_class'];
        }
        // Jenkins likes to see the groups and classnames together. -
        // This might need to be re-addressed when we look at the tests.
        $classname = $test_groups[$test_class] . '.' . $test_class;

        // Cleanup the class, and the parens from the test method name
        $test_method = substr($result['function'], strpos($result['function'], '>') + 1);
        $test_method = substr($test_method, 0, strlen($test_method) - 2);

        //$classes[$test_group][$test_class][$test_method]['classname'] = $classname;
        $result['file'] = substr($result['file'],14); // Trim off /var/www/html
        $classes[$test_group][$test_class][$test_method][] = array(
          'status' => $result['status'],
          'type' => $result['message_group'],
          'message' => strip_tags(htmlspecialchars_decode($result['message'],ENT_QUOTES)),
          'line' => $result['line'],
          'file' => $result['file'],
        );
      }
    }
    $this->_build_xml($classes, $output_directory);
  }

  private function _build_xml($test_result_data, $output_dir) {
    // Maps statuses to their xml element for each testcase.
    $element_map = array(
      'pass' => 'system-out',
      'fail' => 'failure',
      'exception' => 'error',
      'debug' => 'system-err',
    );
    // Create an xml file per group?

    $test_group_id = 0;
    $doc = new DomDocument('1.0');
    $test_suites = $doc->createElement('testsuites');

    // TODO: get test name data from the job.
    $test_suites->setAttribute('name', "TODO SET");
    $test_suites->setAttribute('time', "TODO SET");
    $total_failures = 0;
    $total_tests = 0;
    $total_exceptions = 0;

    // Go through the groups, and create a testsuite for each.
    foreach ($test_result_data as $groupname => $group_classes) {
      $group_failures = 0;
      $group_tests = 0;
      $group_exceptions = 0;
      $test_suite = $doc->createElement('testsuite');
      $test_suite->setAttribute('id', $test_group_id);
      $test_suite->setAttribute('name', $groupname);
      $test_suite->setAttribute('timestamp', date('c'));
      $test_suite->setAttribute('hostname', "TODO: Set Hostname");
      $test_suite->setAttribute('package', $groupname);
      // TODO: time test runs. $test_group->setAttribute('time', $test_group_id);
      // TODO: add in the properties of the job into the test run.

      // Loop through the classes in each group
      foreach ($group_classes as $class_name => $class_methods) {
        foreach ($class_methods as $test_method => $method_results) {
          $test_case = $doc->createElement('testcase');
          $test_case->setAttribute('classname', $groupname . "." . $class_name);
          $test_case->setAttribute('name', $test_method);
          $test_case_status = 'pass';
          $test_case_assertions = 0;
          $test_case_exceptions = 0;
          $test_case_failures = 0;
          $test_output = '';
          $fail_output = '';
          $exception_output = '';
          foreach ($method_results as $assertion) {
            $assertion_result = $assertion['status'] . ": [" . $assertion['type'] . "] Line " . $assertion['line'] . " of " . $assertion['file'] . ":\n" . $assertion['message'] . "\n\n";
            $assertion_result = preg_replace('/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u', '�', $assertion_result);

            // Keep track of overall assersions counts
            if (!isset($assertion_counter[$assertion['status']])) {
              $assertion_counter[$assertion['status']] = 0;
            }
            $assertion_counter[$assertion['status']]++;
            if ($assertion['status'] == 'exception') {
              $test_case_exceptions++;
              $group_exceptions++;
              $total_exceptions++;
              $test_case_status = 'failed';
              $exception_output .= $assertion_result;
            } else if ($assertion['status'] == 'fail'){
              $test_case_failures++;
              $group_failures++;
              $total_failures++;
              $test_case_status = 'failed';
              $fail_output .= $assertion_result;
            }
            elseif (($assertion['status'] == 'debug')) {
              $test_output .= $assertion_result;
            }

            $test_case_assertions++;
            $group_tests++;
            $total_tests++;

          }
          if ($test_case_failures > 0) {
            $element = $doc->createElement("failure");
            $element->setAttribute('message', $fail_output);
            $element->setAttribute('type', "fail");
            $test_case->appendChild($element);
          }

          if ($test_case_exceptions > 0 ) {
            $element = $doc->createElement("error");
            $element->setAttribute('message', $exception_output);
            $element->setAttribute('type', "exception");
            $test_case->appendChild($element);
          }
          $std_out = $doc->createElement('system-out');
          $output = $doc->createCDATASection($test_output);
          $std_out->appendChild($output);
          $test_case->appendChild($std_out);

          // TODO: Errors and Failures need to be set per test Case.
          $test_case->setAttribute('status', $test_case_status);
          $test_case->setAttribute('assertions', $test_case_assertions);
         // $test_case->setAttribute('time', "TODO: track time");

          $test_suite->appendChild($test_case);

        }
      }

      // Should this count the tests as part of the loop, or just array_count?
      $test_suite->setAttribute('tests', $group_tests);
      $test_suite->setAttribute('failures', $group_failures);
      $test_suite->setAttribute('errors', $group_exceptions);
      /* TODO: Someday simpletest will disable or skip tests based on environment
      $test_group->setAttribute('disabled', $test_group_id);
      $test_group->setAttribute('skipped', $test_group_id);
      */
      $test_suites->appendChild($test_suite);
      $test_group_id++;
    }
    $test_suites->setAttribute('tests', $total_tests);
    $test_suites->setAttribute('failures', $total_failures);
   // $test_suites->setAttribute('disabled', "TODO SET");
    $test_suites->setAttribute('errors', $total_exceptions);
    $doc->appendChild($test_suites);
    file_put_contents($output_dir . '/testresults.xml', $doc->saveXML());
    Output::writeln("<info>Reformatted test results written to <options=bold>" . $output_dir . '/testresults.xml</options=bold></info>');
  }

}
