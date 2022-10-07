<?php

  require_once( DIR_LIB . '/logger.inc.php' );

  class DatabaseConnection {

    private $mPdo;

    public function __construct( $user, $password, $database, $host, $port ) {
      $pdoOpts = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
      $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
      $this->mPdo = new PDO( $dsn, $user, $password, $pdoOpts );
    }

    public function getPlacesFromCoords( $coords ):array {
      $select = $this->mPdo->prepare( "SELECT DISTINCT name FROM place WHERE ST_Distance( ST_GeomFromText( :feature ,4326), pos ) < 500" );
      $places = [ ];
      $n = count( $coords );
      Logger::out( "Parsing position 0 of {$n}     " );
      for( $i = 0; $i < $n; $i += 10 ) {
        if( $i >= $n ) break;
        Logger::out( "\rParsing position {$i} of {$n}     " );
        $coord = $coords[ $i ];
        $lon = $coord[ 0 ][ 0 ] + ( $coord[ 0 ][ 1 ] / 60 ) + ( $coord[ 0 ][ 2 ] / 3600000 );
        $lat = $coord[ 1 ][ 0 ] + ( $coord[ 1 ][ 1 ] / 60 ) + ( $coord[ 1 ][ 2 ] / 3600000 );
        $select->execute( [ ':feature' => "POINT({$lon} {$lat})" ] );
        while( $row = $select->fetch( PDO::FETCH_ASSOC ) ) {
          $places[ $row[ 'name' ] ] = $row[ 'name' ];
        }
      }
      Logger::out( "\r{$n} positions checked     \n" );
      return array_values( $places );
    }

    public function fileAlreadyProcessed( $title ):bool {
      $n = $this->mPdo->query( "SELECT COUNT( id_log ) FROM log WHERE name = '{$title}'" )->fetchColumn();
      if( $n == 0 ) {
        $this->mPdo->query( "INSERT INTO log ( name ) VALUES ( '{$title}' )" );
      }
      return $n > 0;
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
