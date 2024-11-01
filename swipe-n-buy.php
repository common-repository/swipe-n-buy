<?php
/**
 * Plugin Name: Swipe n buy
 * Description: A WooCommerce block plugin to make the purchase process easy and fast.
 * Version: 1.0.2
 * Author: bPlugins
 * Author URI: http://bplugins.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain: swipe-n-buy
 */

// ABS PATH
if (!defined('ABSPATH')) {exit;}

// Constant
if ('localhost' === $_SERVER['HTTP_HOST']) {
    $plugin_version = time();
} else {
    $plugin_version = '1.0.2';

}
define('BPWSNB_PLUGIN_VERSION', $plugin_version);

// define('BPWSNB_PLUGIN_VERSION', 'localhost' === $_SERVER['HTTP_HOST']  time() : '1.0.2');
define('BPWSNB_ASSETS_DIR', plugin_dir_url(__FILE__) . 'assets/');
define('BPWSNB_ADMIN_ASSETS_DIR', plugin_dir_url(__FILE__) . 'AdminAssets/');
define('BPWSNB_DIR', plugin_dir_url(__FILE__) );

class BPWSNBSWIPENBUY
{
    public function __construct()
    {
        add_action('wp_enqueue_scripts', [$this, 'csf_specific_page_enqueue']);
        add_action('wp_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('enqueue_block_assets', [$this, 'enqueueScripts']);
        add_action('init', [$this, 'onInit']);
    }

    public function enqueueScripts()
    {
        wp_register_style('bpwsnb-woo-product-swipe-style', plugins_url('dist/style.css', __FILE__), [], BPWSNB_PLUGIN_VERSION);
        wp_register_script('bpwsnb-woo-product-swipe-script', BPWSNB_DIR . 'dist/script.js', ['react', 'react-dom', 'wp-i18n'], BPWSNB_PLUGIN_VERSION, true);

        wp_localize_script('bpwsnb-woo-product-swipe-script', 'wcProducts', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    public function csf_specific_page_enqueue()
    {

        $bpwsnb = json_decode(get_option('bpwsnbSwipeNbuy'), true);
         
        if (!$bpwsnb || !is_array($bpwsnb)) {
            $bpwsnb = [];
        }
         
        if (isset($bpwsnb["route"]) && is_page($bpwsnb["route"])) {
            wp_enqueue_style('bpwsnb-woo-product-swipe-style');
            wp_enqueue_script('bpwsnb-woo-product-swipe-script');

        } else {
            /** Call regular enqueue */
        }
    }

    public function onInit()
    {
        wp_register_style('bpwpsb-woo-swipe-editor-style', plugins_url('dist/editor.css', __FILE__), ['wp-edit-blocks', 'bpwsnb-woo-product-swipe-style'], BPWSNB_PLUGIN_VERSION); // Backend Style

        register_block_type(__DIR__, [
            'editor_style' => 'bpwpsb-woo-swipe-editor-style',
            'render_callback' => [$this, 'render'],
        ]); // Register Block

        wp_set_script_translations('bpwpsb-woo-product-swipe-script', 'product-swiper', plugin_dir_path(__FILE__) . 'languages'); // Translate

    }

    public function render($attributes)
    {
        extract($attributes);

        $className = $className ?? '';
        $bpwpsbBlockClassName = 'wp-block-bpwpsb-product-swiper ' . $className . ' align' . $align;

        wp_enqueue_style('bpwsnb-woo-product-swipe-style');
        wp_enqueue_script('bpwsnb-woo-product-swipe-script');

        ob_start();?>
		<div class='<?php echo esc_attr($bpwpsbBlockClassName); ?>' id='bpwpsb-product-swiper-<?php echo esc_attr($cId) ?>' data-attributes='<?php echo esc_attr(wp_json_encode($attributes)); ?>'></div>

		<?php return ob_get_clean();
    } 

}
new BPWSNBSWIPENBUY();

require_once plugin_dir_path(__FILE__) . '/swipeWoocommerce/activatedWooPlugin.php';
require_once plugin_dir_path(__FILE__) . '/swipeWoocommerce/woocommerce.php';
require_once plugin_dir_path(__FILE__) . '/swipeWoocommerce/Settings.php';