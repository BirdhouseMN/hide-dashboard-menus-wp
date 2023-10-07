<?php
/**
 * Plugin Name: Hide Dashboard Menus WP
 * Description: Hide Dashboard Menus WP allows administrators to customize the WordPress dashboard by hiding non-essential menu items.
 * Version: 1.0
 * Author: Birdhouse Web Design
 */

// AJAX handling
add_action('wp_ajax_update_hidden_items', 'update_hidden_items');
function update_hidden_items() {
    // Verify nonce
    if (check_ajax_referer('update_hidden_items_nonce', 'nonce', false) === false) {
        wp_send_json_error();
        return;
    }
    
    // Validate and sanitize input
    $hiddenItems = isset($_POST['hiddenItems']) ? json_decode(stripslashes($_POST['hiddenItems']), true) : [];
    $sanitizedHiddenItems = array_filter($hiddenItems, function($item) {
        return is_string($item);
    });

    // Save the option and handle errors
    if (update_option('hidden_admin_menu_items', $sanitizedHiddenItems) === false) {
        wp_send_json_error('Failed to update the option');
        return;
    }
    
    wp_send_json_success();
}

// Enqueue custom admin styles and scripts
add_action('admin_enqueue_scripts', 'enqueue_admin_styles');
function enqueue_admin_styles($hook_suffix) {
    // Update the hook_suffix to match the new location under 'Settings'
    if ('settings_page_hide_menu_items' === $hook_suffix) {
        wp_enqueue_style('my-custom-admin-styles', plugins_url('assets/css/hdmw-styles.css', __FILE__));
        wp_enqueue_script('my-custom-admin-scripts', plugins_url('assets/js/hdmw-scripts.js', __FILE__), array(), null, true);

        $hidden_items = get_option('hidden_admin_menu_items', []);
        wp_localize_script('my-custom-admin-scripts', 'hdmwVars', array(
            'hiddenItems' => $hidden_items,
            'nonce' => wp_create_nonce('update_hidden_items_nonce')
        ));
    }
}

// Add submenu under "Settings"
add_action('admin_menu', 'add_hide_admin_menu_items_page_under_settings');
function add_hide_admin_menu_items_page_under_settings() {
    add_submenu_page('options-general.php', 'Hide Menu Items', 'Hide Menu Items', 'manage_options', 'hide_menu_items', 'hide_menu_items_page_callback');
}

// Register the activation hook
register_activation_hook(__FILE__, 'init_hide_admin_menu_items');
function init_hide_admin_menu_items() {
    // Initialization code
}

// Callback for rendering the settings page
function hide_menu_items_page_callback() {
    global $menu;

    if (isset($_POST['submit']) && wp_verify_nonce($_POST['hide_menu_nonce_field'], 'hide_menu_nonce_action')) {
        update_option('hidden_admin_menu_items', isset($_POST['hidden_admin_menu_items']) ? $_POST['hidden_admin_menu_items'] : []);
    }

    $hidden_items = get_option('hidden_admin_menu_items', []);
    echo '<div class="wrap">';
    echo '<h1>Hide Admin Menu Items</h1>';
    echo '<form id="hide-menu-items-form" method="post">';
    wp_nonce_field('hide_menu_nonce_action', 'hide_menu_nonce_field');
    foreach ($menu as $menu_item) {
        if (isset($menu_item[2]) && !empty($menu_item[0]) && $menu_item[4] !== 'wp-menu-separator') {
            if ($menu_item[2] != 'options-general.php') {
                $menu_text = preg_replace('/\d/', '', strip_tags($menu_item[0]));
                echo '<div class="hide-menu-select-item"><label><input type="checkbox" name="hidden_admin_menu_items[]" value="' . esc_attr($menu_item[2]) . '"';
                echo checked(in_array($menu_item[2], $hidden_items), true, false) . '> ' . esc_html($menu_text) . '</label></div>';
            }
        }
    }
    echo '<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes">';
    echo '</form>';
    echo '</div>';
}
