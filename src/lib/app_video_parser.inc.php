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
    private DatabaseConnection $mDb;

    use TApplication {
      run as private defaultRun;
    }

    public function __construct( $parent ) {
      $this->mParent = $parent;
      $this->mIniParser = new IniParser();
    }

    private function doPreOperations():void {
      global $argv;
      if( array_search( 'prune-history', $argv ) !== false ) {
        $this->mDb->pruneHistory();
      }
    }

    public function run():void {

      $this->defaultRun();
      $this->mIniParser->load( DIR_CFG . '/' . FILE_CONFIG );

      $this->mDb = new DatabaseConnection(
        $this->mIniParser->getValue( 'database', 'POSTGRES_USER' ) ?? 'john',
        $this->mIniParser->getValue( 'database', 'POSTGRES_PASSWORD' ) ?? 'secret',
        $this->mIniParser->getValue( 'database', 'POSTGRES_DB' ) ?? 'dashcam',
        $this->mIniParser->getValue( 'database', 'POSTGRES_HOST' ) ?? 'localhost',
        $this->mIniParser->getValue( 'database', 'POSTGRES_PORT' ) ?? 5432 );

      $this->doPreOperations();

      $dirVideo = $this->mIniParser->getValue( 'video_parser', 'DIR_VIDEO' );

      $plexImportFile = $this->mIniParser->getValue( 'video_parser', 'PLEX_IMPORT_FILE' );
      $videofiles = glob( "{$dirVideo}/*.mp4" );
      foreach( $videofiles as $videofile ) {
        $title = pathinfo( $videofile, PATHINFO_FILENAME );
        if( $this->mDb->fileAlreadyProcessed( $title ) ) {
          continue;
        }
        $coords = [];
        $track = `exiftool -ee -"gpslog" -b {$videofile}`;
        $raw = array_map( fn( $ln ) => explode( ',', $ln ), explode( "\n\n", $track ) );
        foreach( $raw as $bin ) {

          for( $i = 0; $i < count( $bin ); $i++ ) {
            switch( $bin[ $i ] ) {
              case 'N':
                $lat = [
                  intval( substr( $bin[ $i - 1 ], 0, 2 ) ),
                  intval( substr( $bin[ $i - 1 ], 2, 2 ) ),
                  intval( substr( $bin[ $i - 1 ], 5, 5 ) )
                ];
                break;

              case 'E':
                $lon = [
                  intval( substr( $bin[ $i - 1 ], 0, 3 ) ),
                  intval( substr( $bin[ $i - 1 ], 3, 2 ) ),
                  intval( substr( $bin[ $i - 1 ], 6, 5 ) )
                ];
                break;
            }
          }
          if( isset( $lat ) && $lat[ 0 ] > 0 && isset( $lon ) && $lon[ 0 ] > 0 ) {
            $key = implode( '', $lon ) . implode( '', $lat );
            $coords[ $key ] = [ $lon, $lat ];
          }
        }
        $placesArr = $this->mDb->getPlacesFromCoords( array_values( $coords ) );
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
