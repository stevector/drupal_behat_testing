<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\definition\AdditionalRepositories
 *
 * PreProcesses DCI_AdditionalRepositories variable, and creates additional
 * 'checkout' entries in the job definition for the repositories defined by
 * that variable.
 */
namespace DrupalCI\Plugin\Preprocess\definition;
use DrupalCI\Console\Output;

/**
 * @PluginID("additionalrepositories")
 */
class AdditionalRepositories {

  /**
   * {@inheritdoc}
   *
   * DCI_AdditionalRepositories_Preprocessor
   *
   * Takes a specially formatted string of repository information, and adds
   * checkout entries for those repositories to the 'setup' stage of the job
   * definition.
   */
  public function process(array &$definition, $repositories) {
    /*
     * The format of the $repositories string is a comma-separated list of
     * values for each checkout, with individual checkout lists separated by
     * semicolons.  The list positions correspond to the following data:
     *
     * Mandatory entries:  <protocol>,<Repository location>,<branch information>,<checkout destination>, ...; where:
     *     <protocol> is the checkout protocol to use.  The only valid value is 'git'.
     *       // TODO: Expand this to support the 'local' checkout entries
     *     <Repository location> is the location of the repository
     *         example: git://git.drupal.org/projects/token.git
     *     <Branch information> is the branch/tag which to be selected
     *         example: 8.x-1.x
     *     <checkout destination> is the directory location within the core checkout to place the code
     *         example: sites/all/modules
     *   Optional entries:  ..., <Checkout depth>;
     *     <checkout depth> corresponds to the --depth parameter on a git checkout, for shallow checkouts.
     *
     * Example String:
     *   git,http://git.drupal.org/project/token.git,8.x-1.x,sites/all/modules/token;git,http://git.drupal.org/project/pathauto.git,8.x-1.x,sites/all/modules/pathauto,1;
     *
     * Desired Result:
     *   array(
     *     'checkout' => array(
     *       [... existing entries ...],
     *       array(
     *         'protocol' => 'git',
     *         'repo' => 'git://git.drupal.org/project/token.git',
     *         'branch' => '8.x-1.x',
     *         'checkout_dir' => 'sites/all/modules/token',
     *       ),
     *       array(
     *         'protocol' => 'git',
     *         'repo' => 'git://git.drupal.org/project/pathauto.git',
     *         'branch' => '8.x-1.x',
     *         'checkout_dir' => 'sites/all/modules/token',
     *         'depth' => 1,
     *       )
     *     )
     *   )
     */

    // Ensure we're passed a non-empty repository string
    if (empty($repositories)) {
      return;
    }

    // There should always be a pre-existing 'checkout' section in the job
    // definition, but in order to future-proof the code, we explicitly check.
    if (empty($definition['setup']['checkout'])) {
      $definition['setup']['checkout'] = [];
    }
    // If it already exists, but has only one entry, we need to normalize it to an array format.
    // Normalize data to the third format, if necessary
    elseif ($definition['setup']['checkout'] == count($definition['setup']['checkout'], COUNT_RECURSIVE)) {
      $definition['setup']['checkout'] = [$definition['setup']['checkout']];
    }

    // Parse the provided repository string into it's components
    $entries = explode(';', $repositories);
    foreach ($entries as $entry) {
      if (empty($entry)) { continue; }
      $components = explode(',', $entry);
      // Ensure we have at least 3 components
      if (count($components) < 4) {
        Output::writeLn("<error>Unable to parse repository information for value <options=bold>$entry</options=bold>.</error>");
        // TODO: Bail out of processing.  For now, we'll just keep going with the next entry.
        continue;
      }
      // Create the job definition entry
      $output = array(
        'protocol' => $components[0],
        'repo' => $components[1],
        'branch' => $components[2],
        'checkout_dir' => $components[3]
      );
      if (!empty($components[4])) {
        $output['depth'] = $components[4];
      }
      $definition['setup']['checkout'][] = $output;
    }
    echo "Checkout: " . print_r($definition['setup']['checkout']);
  }
}

