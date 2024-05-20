<?php
/**
 * Plugin Name: Woocommerce YPF Restricted Country
 * Description: Plugin untuk membatasi negara tertentu pada WooCommerce.
 * Version: 1.0
 * Author: Your Name
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Create custom table on plugin activation
register_activation_hook( __FILE__, 'ypf_create_restricted_countries_table' );
function ypf_create_restricted_countries_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ypf_restricted_countries';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        country_code char(2) NOT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY country_code (country_code)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

// Add filter to restrict countries in frontend
add_filter( 'woocommerce_countries', 'ypf_restricted_woocommerce_countries' );
function ypf_restricted_woocommerce_countries( $countries ) {
    if ( is_admin() ) {
        return $countries; // Do not modify countries in admin
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'ypf_restricted_countries';

    $restricted_countries = $wpdb->get_col( "SELECT country_code FROM $table_name" );

    foreach ( $restricted_countries as $country_code ) {
        unset( $countries[ $country_code ] );
    }

    return $countries;
}

// Add submenu item for plugin settings under WooCommerce menu
add_action( 'admin_menu', 'ypf_restricted_country_menu' );
function ypf_restricted_country_menu() {
    add_submenu_page( 'woocommerce', 'Restricted Countries', 'Restricted Countries', 'manage_options', 'ypf-restricted-country', 'ypf_restricted_country_page' );
}

// Display settings page
function ypf_restricted_country_page() {
    ?>
    <div class="wrap">
        <h1>Restricted Countries</h1>
        <form method="post" action="">
            <?php
            ypf_save_restricted_countries();
            $countries = WC()->countries->get_countries();
            $selected_countries = ypf_get_restricted_countries();
            ?>
            <select multiple="multiple" name="ypf_restricted_countries[]" style="width: 100%; height: 200px;">
                <?php foreach ( $countries as $country_code => $country_name ) : ?>
                    <option value="<?php echo esc_attr( $country_code ); ?>" <?php echo in_array( $country_code, $selected_countries ) ? 'selected' : ''; ?>>
                        <?php echo esc_html( '(' . $country_code . ') ' . $country_name ); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php submit_button( 'Save Changes' ); ?>
        </form>
    </div>
    <?php
}

// Save restricted countries to custom table
function ypf_save_restricted_countries() {
    if ( isset( $_POST['ypf_restricted_countries'] ) ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ypf_restricted_countries';

        // Clear current restricted countries
        $wpdb->query( "TRUNCATE TABLE $table_name" );

        // Insert new restricted countries
        $restricted_countries = $_POST['ypf_restricted_countries'];
        foreach ( $restricted_countries as $country_code ) {
            $wpdb->insert(
                $table_name,
                array(
                    'country_code' => sanitize_text_field( $country_code ),
                )
            );
        }
    }
}

// Get restricted countries from custom table
function ypf_get_restricted_countries() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'ypf_restricted_countries';
    return $wpdb->get_col( "SELECT country_code FROM $table_name" );
}
