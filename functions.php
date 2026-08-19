<?php
/**
 * Storefront engine room
 *
 * @package storefront
 */

/**
 * Assign the Storefront version to a var
 */
$theme              = wp_get_theme( 'storefront' );
$storefront_version = $theme['Version'];

/**
 * Set the content width based on the theme's design and stylesheet.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 980; /* pixels */
}

$storefront = (object) array(
	'version'    => $storefront_version,

	/**
	 * Initialize all the things.
	 */
	'main'       => require 'inc/class-storefront.php',
	'customizer' => require 'inc/customizer/class-storefront-customizer.php',
);

require 'inc/storefront-functions.php';
require 'inc/storefront-template-hooks.php';
require 'inc/storefront-template-functions.php';
require 'inc/wordpress-shims.php';

if ( class_exists( 'Jetpack' ) ) {
	$storefront->jetpack = require 'inc/jetpack/class-storefront-jetpack.php';
}

if ( storefront_is_woocommerce_activated() ) {
	$storefront->woocommerce            = require 'inc/woocommerce/class-storefront-woocommerce.php';
	$storefront->woocommerce_customizer = require 'inc/woocommerce/class-storefront-woocommerce-customizer.php';

	require 'inc/woocommerce/class-storefront-woocommerce-adjacent-products.php';

	require 'inc/woocommerce/storefront-woocommerce-template-hooks.php';
	require 'inc/woocommerce/storefront-woocommerce-template-functions.php';
	require 'inc/woocommerce/storefront-woocommerce-functions.php';
}

if ( is_admin() ) {
	$storefront->admin = require 'inc/admin/class-storefront-admin.php';

	require 'inc/admin/class-storefront-plugin-install.php';
}

/**
 * NUX
 * Only load if wp version is 4.7.3 or above because of this issue;
 * https://core.trac.wordpress.org/ticket/39610?cversion=1&cnum_hist=2
 */
if ( version_compare( get_bloginfo( 'version' ), '4.7.3', '>=' ) && ( is_admin() || is_customize_preview() ) ) {
	require 'inc/nux/class-storefront-nux-admin.php';
	require 'inc/nux/class-storefront-nux-guided-tour.php';
	require 'inc/nux/class-storefront-nux-starter-content.php';
}

/**
 * Note: Do not add any custom code here. Please use a custom plugin so that your customizations aren't lost during updates.
 * https://github.com/woocommerce/theme-customisations
 */








// Programmatically create Haven & Co products directly in WooCommerce
add_action( 'init', 'programmatically_create_haven_products' );

function programmatically_create_haven_products() {
    // Only run when triggered via query parameter by an logged-in admin
    if ( ! current_user_can( 'manage_options' ) || ! isset( $_GET['create_haven_products'] ) ) {
        return;
    }

    $items = array(
        array(
            'sku'   => 'RT-001',
            'name'  => 'Classic canvas tote',
            'price' => '249.00',
            'short' => 'Small-batch canvas tote bag',
            'desc'  => 'Durable small-batch canvas tote bag built from honest materials for everyday carry.',
            'cat'   => 'Bags'
        ),
        array(
            'sku'   => 'RT-002',
            'name'  => 'Ceramic coffee mug',
            'price' => '129.00',
            'short' => 'Handcrafted ceramic mug',
            'desc'  => 'Ceramic coffee mug made for daily use, keeping beverages at temperature.',
            'cat'   => 'Homeware'
        ),
        array(
            'sku'   => 'RT-003',
            'name'  => 'Vanilla soy candle',
            'price' => '179.00',
            'short' => 'Hand-poured soy wax candle',
            'desc'  => 'Hand-poured vanilla soy candle in a reusable glass container.',
            'cat'   => 'Homeware'
        ),
        array(
            'sku'   => 'RT-004',
            'name'  => 'Leather slim wallet',
            'price' => '349.00',
            'short' => 'Genuine leather cardholder',
            'desc'  => 'Slim leather wallet designed for minimalist daily carry.',
            'cat'   => 'Accessories'
        ),
    );

    foreach ( $items as $item ) {
        // Skip if product already exists by SKU
        if ( wc_get_product_id_by_sku( $item['sku'] ) ) {
            continue; 
        }

        $product = new WC_Product_Simple();
        $product->set_name( $item['name'] );
        $product->set_sku( $item['sku'] );
        $product->set_regular_price( $item['price'] );
        $product->set_short_description( $item['short'] );
        $product->set_description( $item['desc'] );
        $product->set_featured( true );
        $product->save();

        // Assign Category
        wp_set_object_terms( $product->get_id(), $item['cat'], 'product_cat' );
    }

    wp_die( 'Success! All 4 products created natively in WooCommerce. You can now refresh your Shop page.' );
}
