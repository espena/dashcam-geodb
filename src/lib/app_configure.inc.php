<?php

  require_once( DIR_LIB . '/i_application.inc.php' );
  require_once( DIR_LIB . '/t_application.inc.php' );
  require_once( DIR_LIB . '/ini_parser.inc.php' );
  require_once( DIR_LIB . '/gml_parser.inc.php' );
  require_once( DIR_LIB . '/unzipper.inc.php' );
  require_once( DIR_LIB . '/logger.inc.php' );

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

    private function parseGML():void {
      $unzipper = new Unzipper( DIR_DATA . '/*GML.zip' );
      $gmlFiles = $unzipper->getFiles();
      try {
        foreach( $gmlFiles as $gmlFile ) {
          if( pathinfo( $gmlFile, PATHINFO_EXTENSION ) == 'gml' &&
              file_exists( $gmlFile ) ) {
            $this->mGmlParser->load( $gmlFile );
          }
        }
      }
      catch( Exception $err ) {
        Logger::out( $err );
      }
      finally {
        $unzipper->deleteFiles();
      }
    }

    public function run():void {
      $this->defaultRun();
      $this->mIniParser->load( DIR_CFG . '/' . FILE_CONFIG );
      $this->parseGML();
      $placesList = $this->mGmlParser->getPlacesList();

      // TODO Import places into PostGIS

    }
  }

?>
