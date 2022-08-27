<?php

  require_once( DIR_LIB . '/i_application.inc.php' );
  require_once( DIR_LIB . '/t_application.inc.php' );
  require_once( DIR_LIB . '/ini_parser.inc.php' );
  require_once( DIR_LIB . '/gml_parser.inc.php' );

  class AppConfigure implements IApplication {

    private IApplication $mParent;
    private IniParser $mIniParser;
    private GmlParser $mGmlParser;

    use TApplication {
      run as private defaultRun;
    }

    public function __construct( $parent ) {
      $this->mParent = $parent;
      $this->mIniParser = new IniParser();
      $this->mGmlParser = new GmlParser();
    }

    public function run():void {
      $this->defaultRun();
    }

  }

?>
