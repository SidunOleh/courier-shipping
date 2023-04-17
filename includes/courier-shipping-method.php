<?php

class Courier_Shipping_Method extends WC_Shipping_Method
{
    public function __construct( $instance_id = 0 )
    {
        $this->id = 'courier'; 
        $this->instance_id = absint( $instance_id );
        $this->enabled = 'yes';
        $this->title = __( 'Courier Shipping' );  
        $this->method_description = __( 'Courier Shipping Method for Woocommerce' ); 
        $this->supports = [
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal',
        ];
      
        $this->init();
        $this->define_hooks();

        $this->method_title = $this->instance_settings[ 'title' ] ?? $this->title;
    }

    private function init()
    {
        $this->init_settings(); 
        $this->init_instance_settings(); 

        $this->instance_form_fields = [
            'title' => [
                'title' => __( 'Title' ),
                'description' => __( 'Method title' ),
                'type' => 'text',
            ],
            'prices' => [
                'title' => __( 'Prices' ),
                'description' => __( 'Price for delivery depends on distance' ),
                'type' => 'textarea',
                'placeholder' => __( 'Example: 2ml:5,5ml:20,...' ),
                'desc_tip' => __( 'Example: 2ml:5,5ml:20,...' ),
            ],
        ];
    }

    private function define_hooks()
    {
        add_action( "woocommerce_update_options_shipping_{$this->id}", [ $this, 'process_admin_options' ] );
        add_filter( 'woocommerce_package_rates', [ $this, 'hide_courier_method' ], 100, 2 );
    }

    public function calculate_shipping( $package = [] )
    {
        $distance = $this->distance( $package[ 'destination' ][ 'postcode' ] );
        if ( $distance === null ) {
            $this->disable_courier_method();
            return;
        }
        $shipping_price = $this->shipping_price( $distance );
        if ( $shipping_price == null ) {
            $this->disable_courier_method();
            return;
        }
        $this->disable_courier_method( false );

        $this->add_rate( [
            'id' => $this->id,
            'label' => $this->method_title,
            'cost' => $shipping_price,
        ] );
    }

    private function distance( $destination )
    {
        $origin = get_option( 'woocommerce_store_postcode' );
        $distance = new Google_Maps_Distance( get_option( 'courier_shipping_method_google_maps_api' ) );

        try {
            return $distance->get( $origin, $destination );
        } catch ( Google_Maps_Distance_Exception $e ) {
            // wc_add_notice( $e->getMessage(), 'error' );
            return;
        }
    }

    private function shipping_price( $distance )
    {
        $shipping_price = null;
        foreach ( $this->prices() as $price ) {
            if ( $price[ 'miles' ] >=  $distance ) {
                $shipping_price = $price[ 'price' ];
                break;
            }
        }

        return $shipping_price;
    }

    private function prices()
    {
        $price_str = trim( $this->instance_settings[ 'prices' ], "\n\r\t\v\x00," );
        
        return array_map( function ( $value ) {
            $items = explode( ':', $value );

            return [
                'miles' => mb_substr( $items[0], 0, -2 ),
                'price' => $items[1],
            ];
        }, explode( ',', $price_str ) );
    }

    private function disable_courier_method( bool $bool = true )
    {
        WC()->session->set( 'disable_courier_method', $bool );
    }

    public function hide_courier_method( $rates, $package )
    {
        if ( WC()->session->get( 'disable_courier_method' ) ) {
            unset( $rates[ 'courier'] );
        }
        
        return $rates;
    }
}