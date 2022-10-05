<?php

  require_once( DIR_LIB . '/logger.inc.php' );

  class DatabaseConnection {

    private $mPdo;

    public function __construct( $user, $password, $database, $host, $port ) {
      $pdoOpts = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
      $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
      $this->mPdo = new PDO( $dsn, $user, $password, $pdoOpts );
    }

    public function importPlaces( $placesList ):void {
      $insert = $this->mPdo->prepare( "INSERT INTO place ( name, pos ) VALUES ( :name, ST_Transform(ST_GeomFromText( :feature, :srid ), 4326 ) )" );
      $n = count( $placesList );
      $i = 0;
      Logger::out( "0 of {$n} geometries stored" );
      foreach( $placesList as $place ) {
        $insert->execute( [
          ':name' => $place[ 0 ],
          ':srid' => $place[ 1 ],
          ':feature' => $place[ 2 ] ] );
        if( ++$i % 1000 == 0 ) {
          Logger::out( "\r{$i} of {$n} geometries stored          " );
        }
      }
      Logger::out( "\rDone saving {$i} geometries             \n" );
    }

  }

?>
