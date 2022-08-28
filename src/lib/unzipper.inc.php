<?php

  require_once( DIR_LIB . '/logger.inc.php' );

  class Unzipper {

    private ZipArchive $mZip;
    private array $mZipFiles;
    private array|null $mFiles = null;

    public function __construct( $pattern ) {
      $this->mZip = new ZipArchive();
      $this->mZipFiles = glob( $pattern );
    }

    public function getFiles():array {
      if( $this->mFiles == null ) {
        srand();
        $this->mFiles = [ ];
        foreach( $this->mZipFiles as $zipFile ) {
          Logger::out( "Extracting {$zipFile} ..." );
          $tmpDir = sys_get_temp_dir() . '/' . substr( hash( 'sha256', rand() ), rand( 0, 47 ), 16 );
          mkdir( $tmpDir, 0777, true );
          if( $this->mZip->open( $zipFile ) ) {
            $this->mZip->extractTo( $tmpDir );
            $this->mZip->close();
          }
          $this->mFiles = array_merge( $this->mFiles, glob( $tmpDir . '/*' ) );
          Logger::out( "\rExtracting {$zipFile} OK!\n" );
        }
      }
      return $this->mFiles;
    }

    public function deleteFiles():void {
      array_walk( $this->mFiles, fn( $s ) => unlink( $s ) );
      $dirs = array_keys( array_flip( array_map( fn( $s ) => dirname( $s ), $this->mFiles ) ) );
      array_walk( $dirs, fn( $s ) => rmdir( $s ) );
    }

  }

?>
