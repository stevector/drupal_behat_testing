<?php
if (preg_match("/\.(engine|inc|info|install|make|module|profile|test|po|sh|.*sql|theme|tpl(\.php)?|xtmpl)/", $_SERVER["REQUEST_URI"])) {
  print "Error\n"; // File type is not allowed
} else
if (preg_match("/(^|\/)\./", $_SERVER["REQUEST_URI"])) {
  return false; // Serve the request as-is
} else
if (file_exists($_SERVER["DOCUMENT_ROOT"] . $_SERVER["SCRIPT_NAME"])) {
  return false;
} else {
  // Feed everything else to Drupal via the "q" GET variable.
  $_GET["q"]=$_SERVER["REQUEST_URI"];
  include("index.php");
}

