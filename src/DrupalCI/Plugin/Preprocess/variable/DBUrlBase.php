<?php
/**
 * @file
 * Contains \DrupalCI\Plugin\Preprocess\variable\DBUser.
 */

namespace DrupalCI\Plugin\Preprocess\variable;

use DrupalCI\Plugin\PluginBase;
use DrupalCI\Plugin\Preprocess\VariableInterface;

abstract class DBUrlBase extends PluginBase implements VariableInterface {

  /**
   * {@inheritdoc}
   */
  public function target() {
    return 'DCI_DBUrl';
  }

  /**
   * Change one part of the URL.
   *
   * @param $db_url
   *   The value of the DCI_DBUrl variable.
   * @param $part
   *   The URL part being replaced. Can be scheme, user, pass, host or path.
   * @param $value
   *   The new value of the URL part.
   *
   * @return string
   *   The new DCI_DBUrl.
   */
  protected function changeUrlPart($db_url, $part, $value) {
    $parts = parse_url($db_url);

    // SQLite does not need any username or password mangling, so just return
    // early with a valid value for --dburl.
    if (isset($parts['scheme']) && strcmp($parts['scheme'], 'sqlite') == 0) {
      return 'sqlite://localhost/sites/default/files/db.sqlite';
    }

    $parts[$part] = $value;
    if (isset($parts['pass']) && !isset($parts['user'])) {
      $parts['user'] = 'user';
    }
    if (isset($parts['scheme']) && strcmp($parts['scheme'], 'mariadb') === 0) {
      $parts['scheme'] = 'mysql';
    }
    $new_url = $parts['scheme'] . '://';
    if (isset($parts['user'])) {
      $new_url .= $parts['user'];
      if (isset($parts['pass'])) {
        $new_url .= ':' . $parts['pass'];
      }
      $new_url .= '@';
    }
    $new_url .= $parts['host'];
    if (isset($parts['path'])) {
      $new_url .= $parts['path'];
    }
    return $new_url;
  }

}
