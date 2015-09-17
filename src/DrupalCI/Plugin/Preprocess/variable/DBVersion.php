<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\DBVersion.
 */

namespace DrupalCI\Plugin\Preprocess\variable;

/**
 * @PluginID("dbversion")
 */
class DBVersion extends DBUrlBase {

  /**
   * {@inheritdoc}
   */
  public function process($db_url, $source_value) {
    $mod_value = explode(':', $source_value, 2)[0];
    $dbtype = explode('-', $mod_value, 2)[0];
    $host_part = str_replace([':', '.'], '-', $source_value);
    $host = 'drupaltestbot-db-' . $host_part;
    $db_url = $this->changeUrlPart($db_url, 'scheme', $dbtype);
    $db_url = $this->changeUrlPart($db_url, 'host', $host);
    return $db_url;
  }

}
