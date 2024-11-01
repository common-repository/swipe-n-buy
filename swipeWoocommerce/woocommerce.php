<?php if (!defined('ABSPATH')) {exit;}

class BPWSNGETPRODUCTS
{
    public function __construct()
    {
        add_action('wp_ajax_bpwsnb_create_page', [$this, 'bpwsnb_create_page']);
        add_action('wp_ajax_nopriv_bpwsnb_create_page', [$this, 'bpwsnb_create_page']);

        add_action('wp_ajax_bpwsnb_get_category', [$this, 'bpwsnb_get_category']);
        add_action('wp_ajax_nopriv_bpwsnb_get_category', [$this, 'bpwsnb_get_category']);

        add_action('wp_ajax_bpswb_remove_wish_list', [$this, 'bpswb_remove_wish_list']);
        add_action('wp_ajax_nopriv_bpswb_remove_wish_list', [$this, 'bpswb_remove_wish_list']);

        add_action('wp_ajax_bpswb_all_wish_list', [$this, 'bpswb_all_wish_list']);
        add_action('wp_ajax_nopriv_bpswb_all_wish_list', [$this, 'bpswb_all_wish_list']);

        add_action('wp_ajax_bpswb_add_wishlist', [$this, 'bpswb_add_wishlist']);
        add_action('wp_ajax_nopriv_bpswb_add_wishlist', [$this, 'bpswb_add_wishlist']);

        add_action('wp_ajax_bpswb_cart_products', [$this, 'bpswb_cart_products']);
        add_action('wp_ajax_nopriv_bpswb_cart_products', [$this, 'bpswb_cart_products']);

        add_action('wp_ajax_bpswb_add_to_cart', [$this, 'bpswb_add_to_cart']);
        add_action('wp_ajax_nopriv_bpswb_add_to_cart', [$this, 'bpswb_add_to_cart']);

        add_action('wp_ajax_bpswb_get_products', [$this, 'bpswb_get_products']);
        add_action('wp_ajax_nopriv_bpswb_get_products', [$this, 'bpswb_get_products']);

    }

    public function bpwsnb_create_page() {

        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $page_id = get_option('bpwsnb_page_id', null);
        $route = sanitize_text_field($_GET['route']) ?? false;
        $attributes = json_decode(stripslashes($_GET['data']), true); 
        $convert = wp_json_encode(wp_parse_args($attributes,  
                        array(
                            "route" => "quick-view",
                            "categories" => array(
                                ""
                            ),
                            "limit" => -1,
                            "order_by" => "ID",
                            "layout" => array(
                                "desktop" => "swipe",
                                "tablet" => "swipe",
                                "mobile" => "swipe"
                            ),
                            "width" => 100,
                            "layoutAlign" => "left",
                            "columnGap" => "20px",
                            "rowGap" => "30px",
                            "columns" => array(
                                "desktop" => 2,
                                "tablet" => 1,
                                "mobile" => 1
                            ),
                            "btnBorder" => array(
                                "width" => "1px",
                                "color" => "#000",
                                "radius" => "50%"
                            ),
                            "btnSize" => array (
                               "desktop" => 60,
                               "tablet" => 50,
                               "mobile" => 40 
                            ),
                            "btnActiveColor" => "#b1b1b1ff"
                        )
                    ));
 
        if ($page_id) {
            $my_post = array(
                'ID' => $page_id,
                'post_title' =>  $route,
                'post_content' => "<div id='bpwpsbProductSwiper' class='bpwpsb-product-swiper' data-attributes='" . esc_attr($convert) . "'></div>",
                'post_status' => 'publish',
                'post_name' =>  $route,
            );
            wp_update_post($my_post);

        } else {

            $page_id = wp_insert_post(
                array(
                    'post_title' => $route,
                    'post_content' => "<div id='bpwpsbProductSwiper' class='bpwpsb-product-swiper' data-attributes='" . esc_attr($convert) . "'></div>",
                    'post_status' => 'publish',
                    'post_type' => 'page',
                    'post_name' => $route,
                ),
            );
            update_option('bpwsnb_page_id', $page_id);
        }

        wp_send_json_success(  $attributes);
    }

    public function bpwsnb_get_category() {

        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

         $args = array(
            'taxonomy'     => 'product_cat',
            'orderby'      => 'name',  
		);

		$categories = get_categories( $args );
        $tempCategories = [];

        foreach ($categories as $category) {
            $tempCategories[] = [
                'id' => $category->term_id,
                'name' => $category->name,
            ];
        }
        wp_send_json_success($tempCategories);

    }

    public function bpswb_remove_wish_list()
    {
        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $product_id = sanitize_text_field($_GET['product_id']) ?? false;

        $wishlist = WC()->session->get('wishlist');
        if ($wishlist) {
            $key = array_search($product_id, $wishlist);
            if ($key !== false) {
                unset($wishlist[$key]);
                WC()->session->set('wishlist', $wishlist);
            }
        }

        wp_send_json_success($wishlist);
    }

    public function bpswb_all_wish_list()
    {
        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $wishlist = WC()->session->get('wishlist');
        $tempWishlist = [];
        if ($wishlist) {
            foreach ($wishlist as $product_id) {
                $tempWishlist[] = [
                    'id' => $product_id,
                ];
            }
        }
        wp_send_json_success($tempWishlist);
    }

    public function bpswb_add_wishlist()
    {
        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $product_id = sanitize_text_field($_GET['product_id']) ?? false;

        $wishlist = WC()->session->get('wishlist');
        if (!$wishlist) {
            $wishlist = array();
        }
        if (!in_array($product_id, $wishlist)) {
            $wishlist[] = $product_id;
        }
        $response = WC()->session->set('wishlist', $wishlist);

        wp_send_json_success($response);

    }

    public function bpswb_cart_products()
    {
        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        global $woocommerce;

        $products = $woocommerce->cart->get_cart();
        $tempProducts = [];

        foreach ($products as $product) {
            $tempProducts[] = [
                'id' => $product['product_id'],
                'quantity' => $product['quantity'],
            ];
        }
        wp_send_json_success($tempProducts);
    }

    public function bpswb_add_to_cart()
    {

        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $product_id = sanitize_text_field($_GET['product_id']) ?? false;
        $quantity = sanitize_text_field($_GET['quantity']) ?? false;

        $response = WC()->cart->add_to_cart($product_id, $quantity);
        wp_send_json_success($response);

    }

    public function bpswb_get_products()
    {
        if (!wp_verify_nonce(sanitize_text_field($_GET['nonce']), 'wp_rest')) {
            wp_die();
        }

        $limit = sanitize_text_field($_GET['limit']) ?? false;
        $order_by = sanitize_text_field($_GET['order_by']) ?? false;
        $order = sanitize_text_field($_GET['order']) ?? false;
        $category_id = sanitize_text_field($_GET['categories']) ?? false; // Changed this line

        // Correct the order logic
        $order = strtolower($order) === 'desc' ? 'DESC' : 'ASC'; // Fixed the logic

        // Initialize the terms array if category_id is not empty
        $terms = !empty($category_id) ? [$category_id] : [];
         
        $query_args = [
            'posts_per_page' => $limit,
            'status' => 'publish',
            'orderby' => $order_by,
            'order' => $order,
        ];

        if (!empty($terms)) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $terms,
                    'operator' => 'IN'
                ]
            ];
        }

        $products = (array) wc_get_products($query_args);

        if (empty($products)) {
            wp_send_json_success("Product Not Found");
            return;
        }

        $tempProducts = [];

        foreach ($products as $product) {

            $images = [];
            foreach ($product->get_gallery_image_ids() as $id) {
                $image = wp_get_attachment_image_src($id, 'medium');
                $images[] = [
                    'src' => $image[0],
                    'height' => $image[1],
                    'width' => $image[2],
                ];
            }

            $thumbnail = wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'single-post-thumbnail');

            $tempProducts[] = [
                'id' => $product->get_id(),
                'name' => $product->get_name(),
                'desc' => $product->get_description(),
                'price' => $product->get_price(),
                'regular_price' => $product->get_regular_price(),
                'sale_price' => $product->get_sale_price(),
                'currency' => html_entity_decode(get_woocommerce_currency_symbol()),
                'images' => $images,
                'thumbnail' => [
                    'src' => $thumbnail[0],
                    'height' => $thumbnail[1],
                    'width' => $thumbnail[2],
                ],
            ];
        }

        wp_send_json_success($tempProducts);
    }

}
new BPWSNGETPRODUCTS();

// add_action("wp_footer", function() {

//     $terms = []; // Define your term IDs here, e.g., [20, 21, 22]
    
//     $query_args = [
//         'posts_per_page' => -1,
//         'status' => 'publish',
//     ];

//     // Add tax_query only if terms are specified
//     if (!empty($terms)) {
//         $query_args['tax_query'] = [
//             [
//                 'taxonomy' => 'product_cat',
//                 'field'    => 'term_id',
//                 'terms'    => $terms,
//                 'operator' => 'IN'
//             ]
//         ];
//     }

//     $products = (array) wc_get_products($query_args);
//     var_dump($products);

// });