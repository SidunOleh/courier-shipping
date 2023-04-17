<?php

class Courier_Shipping
{
    public function run()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }

    private function load_dependencies()
    {
        require_once plugin_dir_path( __FILE__ ) . 'google-maps-distance.php';
    }

    private function define_hooks()
    {
        add_action( 'woocommerce_shipping_init', [ $this, 'courier_shipping_method' ] );
        add_filter( 'woocommerce_shipping_methods', [ $this, 'add_courier_shipping_method' ] );
        add_filter( 'woocommerce_get_sections_shipping', [ $this, 'add_courier_shipping_method_section' ] );
        add_filter( 'woocommerce_get_settings_shipping', [ $this, 'add_courier_shipping_method_settings' ], 10, 2 );
    }

    public function courier_shipping_method()
    {
        require_once plugin_dir_path( __FILE__ ) . 'courier-shipping-method.php';
    }

    public function add_courier_shipping_method( $methods )
    {
        $methods[ 'courier' ] = 'Courier_Shipping_Method';
        
        return $methods;
    }

    public function add_courier_shipping_method_section( $sections )
    {
        $sections[ 'courier_shipping' ] = 'Courier Shipping';

        return $sections;
    }

    public function add_courier_shipping_method_settings( $settings, $current_section )
    {
        if ( $current_section != 'courier_shipping' ) {
            return $settings;
        }

        $courier_settings[] = [
            'name' => 'Courier Shipping settings',
            'type' => 'title',
        ];
        $courier_settings[] = [
            'id' => 'courier_shipping_method_google_maps_api',
            'name' => 'Google Maps API key',
            'type' => 'text',
        ];
        $courier_settings[] = [
            'type' => 'sectionend',
        ];

        return $courier_settings;
    }
}