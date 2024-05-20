<?php
/**
 * Plugin Name: Woocommerce YPF Restricted Country
 * Description: Plugin to restrict specific countries on WooCommerce.
 * Version: 1.0.1
 * Author: Ardi FinPR
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Add filter to restrict countries
add_filter( 'woocommerce_countries', 'ypf_restricted_woocommerce_countries' );
function ypf_restricted_woocommerce_countries( $countries ) {
    $remove_countries = get_option( 'ypf_restricted_countries', array('BE','CA','CU','FR','IR','JP','KP','KR','LB','LY','MM','PK','SO','SD','SY','US','UM') );
    foreach ( $remove_countries as $country_code ) {
        unset( $countries[ $country_code ] );
    }
    return $countries;
}

// Add menu item for plugin settings
add_action( 'admin_menu', 'ypf_restricted_country_menu' );
function ypf_restricted_country_menu() {
    add_menu_page( 'Restricted Countries', 'Restricted Countries', 'manage_options', 'ypf-restricted-country', 'ypf_restricted_country_page', 'dashicons-admin-site', 56 );
}

// Display settings page
function ypf_restricted_country_page() {
    ?>
    <div class="wrap">
        <h1>Restricted Countries</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'ypf_restricted_country_group' );
            do_settings_sections( 'ypf-restricted-country' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings, section, and fields
add_action( 'admin_init', 'ypf_restricted_country_settings' );
function ypf_restricted_country_settings() {
    register_setting( 'ypf_restricted_country_group', 'ypf_restricted_countries' );

    add_settings_section( 'ypf_restricted_country_section', 'Set Restricted Countries', 'ypf_restricted_country_section_callback', 'ypf-restricted-country' );

    add_settings_field( 'ypf_restricted_countries_field', 'Restricted Countries', 'ypf_restricted_countries_field_callback', 'ypf-restricted-country', 'ypf_restricted_country_section' );
}

function ypf_restricted_country_section_callback() {
    echo 'Select the countries you want to restrict from WooCommerce.';
}

function ypf_restricted_countries_field_callback() {
    $countries = WC()->countries->get_countries();
    $selected_countries = get_option( 'ypf_restricted_countries', array() );
    ?>
    <select multiple="multiple" name="ypf_restricted_countries[]" style="width: 100%; height: 200px;">
        <?php foreach ( $countries as $country_code => $country_name ) : ?>
            <option value="<?php echo esc_attr( $country_code ); ?>" <?php echo in_array( $country_code, $selected_countries ) ? 'selected' : ''; ?>>
                <?php echo esc_html( $country_name ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}