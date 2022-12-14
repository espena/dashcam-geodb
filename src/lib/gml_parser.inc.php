<?php

  require_once( DIR_LIB . '/i_parser.inc.php' );
  require_once( DIR_LIB . '/logger.inc.php' );

  class gmlParser implements IParser {

    private DOMDocument $mDoc;
    private DOMXPath $mXPath;
    private array $mPlaces;

    public function __construct() {
      $this->mDoc = new DOMDocument();
      $this->mPlaces = [ ];
    }

    private function getSRID():string {
      $gmlSRS = $this->mXPath->query( '//gml:Envelope' )->item( 0 )->attributes->getNamedItem( 'srsName' )->nodeValue;
      preg_match( '/^urn:ogc:def:crs:EPSG::([0-9]+)$/', $gmlSRS, $m );
      return $m[ 1 ];
    }

    public function load( $filename ):void {
      Logger::out( "Loading {$filename} (may take a couple of minutes)\n" );
      $this->mDoc->load( $filename );
      $this->mXPath = new DOMXPath( $this->mDoc );
      $places = $this->mXPath->query( '/gml:FeatureCollection/gml:featureMember/app:Sted' );
      $srid = $this->getSRID();
      $gml2postgis = [
        'gml:Point'      => 'Point(%s)',
        'gml:LineString' => 'LineString(%s)'
      ];
      /*
      $geomNodes = [
        './/gml:Point//gml:pos',
        './/gml:LineString//gml:posList'
      ];
      */
      $geomNodes = [
        './/gml:Point//gml:pos'
      ];
      foreach( $places as $place ) {
        $coords = $this->mXPath->query( implode( '|', $geomNodes ), $place );
        $names = $this->mXPath->query( './/app:stedsnavn/app:Stedsnavn/app:skrivemåte/app:Skrivemåte/app:komplettskrivemåte', $place );
        if( $coords->length > 0 && $names->length == 1 ) {
          foreach( $coords as $coord ) {
            $gmlType = $this->mXPath->query( '../.', $coord )->item( 0 )->nodeName;
            if( preg_match_all( '/[\d\.]+ [\d\.]+\s?+/', $coord->nodeValue, $m ) ) {
              $coordList = implode( ', ', array_map( fn( $coord ) => trim( $coord ), $m[ 0 ] ) );
              $feature = sprintf( $gml2postgis[ $gmlType ], $coordList );
              $this->mPlaces[ ] = [
                $names->item( 0 )->nodeValue,
                $srid,
                $feature
              ]; } } }
      }
    }

    public function getPlacesList():array {
      return $this->mPlaces;
    }

  }

?>
