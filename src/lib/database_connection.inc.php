<?php

  class DatabaseConnection {

    private $mPdo;

    public function __construct( $user, $password, $database, $host, $port ) {

    }

    public function importPlaces( $placesList ):void {
      $pdoOpts = [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION ];
      $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
      $pdo = new PDO( $dsn, $user, $password, $pdoOpts );
      $pdo->prepare( '' );

      $pdo = null;
    }

  }

?>
