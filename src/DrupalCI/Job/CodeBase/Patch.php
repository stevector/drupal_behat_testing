<?php

/**
 * @file
 * Contains \DrupalCI\Job\CodeBase\Patch
 */

namespace DrupalCI\Job\CodeBase;


class Patch {

  // Local or Remote
  // For future use in rendering the 'fetch' step optional.  (i.e.
  protected $type = 'remote';
  public function getType() {  return $this->type;  }
  public function setType($type)  {  $this->type = $type;  }

  // Source
  protected $source;
  public function getSource() {  return $this->source;  }

  // Apply directory
  protected $apply_dir;
  public function getApplyDir() {  return $this->apply_dir;  }
  public function setApplyDir($directory) {  $this->apply_dir = $directory;  }

  // Files modified by this patch
  protected $modified_files;
  public function getModifiedFiles() {
    // if not set $this->modified_files, calculate and set $this->modified files
    // then
    return $this->modified_files;
  }

  // Constructor
  public function __construct($patch_string) {
    $parsed_source = explode(', ', $patch_string);
    $this->apply_dir = (!empty($parse_source[1])) ? $parse_source[1] : ".";
    $source = $parsed_source[0];
    $this->source = $source;

    // Determine whether passed a URL or local file
    $type = filter_var($source, FILTER_VALIDATE_URL) ? "remote" : "local";
    $this->setType($type);
  }

  // Obtain remote file
  public function download() {

  }

  // Validate file exists
  public function validate_file() {

  }

  // Validate target directory exists
  public function validate_target() {

  }

  // Apply the patch
  public function apply() {

  }

}