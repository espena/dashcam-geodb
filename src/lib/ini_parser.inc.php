<?php

  require_once( DIR_LIB . '/i_parser.inc.php' );

  class iniParser implements IParser {

    private array $mIniData;

    public function __construct() {  }

    public function load( $filename ):void {
      $this->mIniData = parse_ini_file( $filename, true );
    }

    public function getValue( $section, $key ):string|array|null {
      return $this->mIniData[ $section ][ $key ] ?? null;
    }

  }

?>
