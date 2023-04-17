<?php

class Google_Maps_Distance
{
    private $url;
    
    private $api_key;

    public function __construct( $api_key )
    {
        $this->url = 'https://maps.googleapis.com/maps/api/directions/json';
        $this->api_key = $api_key;
    }

    public function get( $origin, $destination ) : int
    {
        $response = $this->request( $origin, $destination );
        $meters = $response[ 'routes' ][0][ 'legs' ][0][ 'distance' ][ 'value' ];
        $miles = round( $meters / 1609 );

        return $miles;
    }

    private function request( $origin, $destination )
    {
        $response = wp_remote_get( $this->url( $origin, $destination ) );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( $body[ 'status' ] != 'OK' ) {
            throw new Google_Maps_Distance_Exception( $body[ 'status' ] );
        }

        return $body;
    }

    private function url( $origin, $destination )
    {
        return "{$this->url}?origin={$origin}&destination={$destination}&key={$this->api_key}";
    }
}

class Google_Maps_Distance_Exception extends Exception
{

}