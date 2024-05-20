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

// Add filter to restrict countries
add_filter( 'woocommerce_countries', 'ypf_restricted_woocommerce_countries' );
function ypf_restricted_woocommerce_countries( $countries ) {
    if ( is_admin() ) {
        return $countries; // Do not modify countries in admin
    }
    $remove_countries = get_option( 'ypf_restricted_countries', array() );
    if ( ! is_array( $remove_countries ) ) {
        $remove_countries = array();
    }
    foreach ( $remove_countries as $country_code ) {
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
    register_setting( 'ypf_restricted_country_group', 'ypf_restricted_countries', array(
        'type' => 'array',
        'sanitize_callback' => 'ypf_sanitize_countries',
        'default' => array()
    ));

    add_settings_section( 'ypf_restricted_country_section', 'Set Restricted Countries', 'ypf_restricted_country_section_callback', 'ypf-restricted-country' );

    add_settings_field( 'ypf_restricted_countries_field', 'Restricted Countries', 'ypf_restricted_countries_field_callback', 'ypf-restricted-country', 'ypf_restricted_country_section' );
}

function ypf_restricted_country_section_callback() {
    echo 'Select the countries you want to restrict from WooCommerce.';
}

function ypf_restricted_countries_field_callback() {
    $countries = WC()->countries->get_countries();
    $selected_countries = get_option( 'ypf_restricted_countries', array() );
    if ( ! is_array( $selected_countries ) ) {
        $selected_countries = array();
    }
    ?>
    <div id="ypf-restricted-countries-container">
        <?php foreach ( $selected_countries as $country_code ) : ?>
            <div class="ypf-restricted-country">
                <select name="ypf_restricted_countries[]" style="width: 80%; display: inline-block;">
                    <?php foreach ( $countries as $code => $name ) : ?>
                        <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $country_code, $code ); ?>>
                            <?php echo esc_html( '(' . $code . ') ' . $name ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="button" class="button ypf-remove-country" style="display: inline-block;">Remove</button>
            </div>
        <?php endforeach; ?>
    </div>
    <button type="button" class="button" id="ypf-add-country">Add Country</button>
    <script>
    jQuery(document).ready(function($) {
        $('#ypf-add-country').on('click', function() {
            var $container = $('#ypf-restricted-countries-container');
            var $template = $('<div class="ypf-restricted-country"><select name="ypf_restricted_countries[]" style="width: 80%; display: inline-block;"><?php foreach ( $countries as $code => $name ) : ?><option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( '(' . $code . ') ' . $name ); ?></option><?php endforeach; ?></select><button type="button" class="button ypf-remove-country" style="display: inline-block;">Remove</button></div>');
            $container.append($template);
        });

        $(document).on('click', '.ypf-remove-country', function() {
            $(this).closest('.ypf-restricted-country').remove();
        });
    });
    </script>
    <?php
}

function ypf_sanitize_countries( $input ) {
    // Ensure the input is an array and sanitize each element
    return array_map( 'sanitize_text_field', (array) $input );
}
