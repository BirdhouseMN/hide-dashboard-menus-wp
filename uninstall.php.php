<?php
// If uninstall is not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}

// Delete option from the database
delete_option('hidden_admin_menu_items');
