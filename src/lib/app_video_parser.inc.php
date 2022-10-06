<?php

  require_once( DIR_LIB . '/i_application.inc.php' );
  require_once( DIR_LIB . '/t_application.inc.php' );
  require_once( DIR_LIB . '/ini_parser.inc.php' );
  require_once( DIR_LIB . '/gml_parser.inc.php' );
  require_once( DIR_LIB . '/unzipper.inc.php' );
  require_once( DIR_LIB . '/logger.inc.php' );
  require_once( DIR_LIB . '/database_connection.inc.php' );

  class AppVideoParser implements IApplication {

    private IApplication $mParent;
    private IniParser $mIniParser;

    use TApplication {
      run as private defaultRun;
    }

    public function __construct( $parent ) {
      $this->mParent = $parent;
      $this->mIniParser = new IniParser();
    }

    public function run():void {

      $this->defaultRun();
      $this->mIniParser->load( DIR_CFG . '/' . FILE_CONFIG );

      $db = new DatabaseConnection(
        $this->mIniParser->getValue( 'database', 'POSTGRES_USER' ) ?? 'john',
        $this->mIniParser->getValue( 'database', 'POSTGRES_PASSWORD' ) ?? 'secret',
        $this->mIniParser->getValue( 'database', 'POSTGRES_DB' ) ?? 'dashcam',
        $this->mIniParser->getValue( 'database', 'POSTGRES_HOST' ) ?? 'localhost',
        $this->mIniParser->getValue( 'database', 'POSTGRES_PORT' ) ?? 5432 );

      $dirVideo = $this->mIniParser->getValue( 'video_parser', 'DIR_VIDEO' );

      $plexImportFile = $this->mIniParser->getValue( 'video_parser', 'PLEX_IMPORT_FILE' );
      $videofiles = glob( "{$dirVideo}/*.mp4" );
      foreach( $videofiles as $videofile ) {
        $title = pathinfo( $videofile, PATHINFO_FILENAME );
        if( $db->fileAlreadyProcessed( $title ) ) {
          continue;
        }
        $coords = [];
        $track = `exiftool -ee -"gpslog" -b {$videofile}`;
        $raw = array_map( fn( $ln ) => explode( ',', $ln ), explode( "\n\n", $track ) );
        foreach( $raw as $bin ) {
          for( $i = 0; $i < count( $bin ); $i++ ) {
            switch( $bin[ $i ] ) {
              case 'N':
                $lat = floatval( $bin[ $i - 1 ] ?? 0 ) * 0.01;
                break;
              case 'E':
                $lon = floatval( $bin[ $i - 1 ] ?? 0 ) * 0.01;
                break;
            }
          }
          if( !empty( $lat ) && !empty( $lon ) ) {
            $coords[ "{$lon} {$lat}" ] = "{$lon} {$lat}";
          }
        }
        $placesArr = $db->getPlacesFromCoords( array_values( $coords ) );
        $placesStr = implode( ", ", $placesArr );
        if( count( $placesArr ) > 0 ) {
          file_put_contents( $plexImportFile, "UPDATE metadata_items SET summary = '{$placesStr}' WHERE title = '{$title}';\n", FILE_APPEND );
          $index = 0;
          $dict = [ ];
          foreach( $placesArr as $place ) {
            if( !isset( $dict[ $place ] ) ) {
              file_put_contents( $plexImportFile, "REPLACE INTO tags ( tag, tag_type ) VALUES ( '{$place}', 1 );\n", FILE_APPEND );
              $dict[ $place ] = true;
            }
            file_put_contents( $plexImportFile, "INSERT INTO taggings ( \"tag_id\", \"metadata_item_id\", \"index\" ) VALUES( ( SELECT id FROM tags WHERE tag = '{$place}' ), ( SELECT id FROM metadata_items WHERE title = '{$title}' ), {$index} );\n", FILE_APPEND );
            $index++;
          }
        }
      }
    }
  }

?>
