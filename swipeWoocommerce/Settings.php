<?php if (!defined('ABSPATH')) {exit;}

if( !class_exists('BPWSNB_SETTINGS') ){
    class BPWSNB_SETTINGS {
        public function __construct(){
            add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'] );
            add_action( 'admin_menu', [$this, 'adminMenu'] );
            add_action( 'admin_init', [$this, 'register_settings'] );
            add_action( 'rest_api_init', [$this, 'register_settings'] );
        }

        function admin_enqueue_scripts( $hook ){
            wp_register_style('bpwsnb-settings', BPWSNB_DIR . 'dist/settings.css', [ 'wp-components' ], BPWSNB_PLUGIN_VERSION);
            wp_register_script('bpwsnb-settings', BPWSNB_DIR . 'dist/settings.js', [ 'react', 'react-dom', 'wp-api', 'wp-components', 'wp-i18n', 'wp-data', 'wp-blocks', 'wp-block-editor' ], BPWSNB_PLUGIN_VERSION, true);

            wp_localize_script('bpwsnb-settings', 'wcCategories', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_rest'),
            ]);
        }

        function adminMenu(){
            add_submenu_page(
                'options-general.php',
                __( 'Swipe n Buy', 'bpwsnb-dashboard' ),
                __( 'Swipe n Buy', 'bpwsnb-dashboard' ),
                'manage_options',
                'swipe-n-buy',
                [$this, 'swipeNBuyPage']
            );
        }

        function swipeNBuyPage(){
            wp_enqueue_script('bpwsnb-settings');
            wp_enqueue_style('bpwsnb-settings');
            ?>
                <div id="bpwsnbDashboard"></div>
            <?php
        }

        function register_settings(){
            register_setting( "cpr", "bpwsnbSwipeNBuy", array(
                'show_in_rest' => array(
                    'name' => "bpwsnbSwipeNBuy",
                    'schema' => array(
                        'type'  => 'string',
                    ),
                ),
                'type' => 'string',
                'default' => '{}',
                'sanitize_callback' => 'wp_kses_post',
            ));
        }
    }
    new BPWSNB_SETTINGS();
}
