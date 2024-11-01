<?php if (!defined('ABSPATH')) {exit;}

if( !class_exists('BPWSNB_ACTIVATED_WOO_PLUGIN') ){
    class BPWSNB_ACTIVATED_WOO_PLUGIN
    {
        public function __construct()
        {
            add_action('plugins_loaded', [$this, 'pluginsLoaded']);
        }

        public function pluginsLoaded()
        {
            if (!did_action('woocommerce_loaded')) {
                add_action('admin_notices', [$this, 'wooCommerceNotLoaded']);
                return;
            }
        }

        public function wooCommerceNotLoaded()
        {
            if (!current_user_can('activate_plugins')) {
                return;
            }

            $woocommerce = 'woocommerce/woocommerce.php';

            if ($this->isPluginInstalled($woocommerce)) {
                $activationUrl = wp_nonce_url('plugins.php?action=activate&amp;plugin=' . $woocommerce . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $woocommerce);

                $message = sprintf( '%1$s Swipe n buy %2$s requires %1$sWooCommerce%2$s plugin to be active. Please activate WooCommerce to continue.',  "<strong>", "</strong>");

                $button_text = __('Activate WooCommerce', 'swipe-n-buy');
            } else {
                $activationUrl = wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=woocommerce'), 'install-plugin_woocommerce');

                $message = sprintf('%1$s Swipe n buy .%2$s requires %1$sWooCommerce%2$s plugin to be installed and activated. Please install WooCommerce to continue.', '<strong>', '</strong>');

                $button_text = __('Install WooCommerce', 'swipe-n-buy');
            }

            $button = '<p><a href="' . esc_url($activationUrl) . '" class="button-primary">' . esc_html($button_text) . '</a></p>';
    
            printf( '<div class="error"><p>%1$s</p>%2$s</div>',$message, $button);
        }

        public function isPluginInstalled($basename)
        {
            if (!function_exists('get_plugins')) {
                include_once ABSPATH . '/wp-admin/includes/plugin.php';
            }

            $installedPlugins = get_plugins();

            return isset($installedPlugins[$basename]);
        }
    }
    new BPWSNB_ACTIVATED_WOO_PLUGIN;
}
