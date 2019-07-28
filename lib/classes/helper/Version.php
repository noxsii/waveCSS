<?php
/**
 * Copyright (c) 2019.
 *
 * noxsi _WAVE.
 * by noxsi.io
 * made in Stuttgart - Germany
 * develope by Dennis Schirra
 *
 * @link   https://noxsi.io
 * @contact dennis@noxsi.io
 *
 * @file   : Version.php
 * @project: waveCSS
 * @module : noxsi.io
 * @last   edit: 28.07.2019 15:44
 * @user   inkmu
 *
 */
namespace noxsi\helper;

class Version {

  public $wp_version;


    public function versionError($wp_version) {

      // the error array
      $versionError = array(
          'version' => '<div class="error">noxsi waveCSS requires WordPress 3.0 or newer. <a href="https://codex.wordpress.org/Upgrading_WordPress">Please update!</a></div>',
          'data_saved' => 'Data Saved!',
          'invalid_request' => 'Invalid Request!',
          'duplicate_complete' => 'Duplication process is complete!'
      );

      // check version and get error
      if ( !version_compare($wp_version,"3.0",">=")) {
          die ($versionError['version']);
      }
  }

    /**
     * @return mixed
     */
    public function getWpVersion()
    {
        return $this->wp_version;
    }

    /**
     * @param mixed $wp_version
     */
    public function setWpVersion($wp_version): void
    {
        $this->wp_version = $wp_version;
    }

}
