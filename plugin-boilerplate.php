<?php
/**
 * @package Plugin Boilerplate
 */
/*
Plugin Name: Plugin Boilerplate
Plugin URI: https://github.com/WARPAINTMedia/wordpress-plugin
Description: A brief description of the plugin.
Version: 1.0.0
Author: WARPAINT Media
Author URI: http://warpaintmedia.ca
License: MIT License
*/

define("PLUGIN_TITLE", "My Awesome Plugin");
define("PLUGIN_TABLE", "myplugin");
define("PLUGIN_MENU", "My Plugin");
define("PLUGIN_OPTIONS", "myplugin-options");
define("PLUGIN_ROUTE", "myplugin-admin-page");
define("PLUGIN_AJAX", "myplugin_ajax");
define("PLUGIN_CSS", "plugin-boilerplate.css");

// no direct access allowed
defined( 'ABSPATH' ) or die( 'No drect script access' );

function plugin_install() {
  global $wpdb;
  $wpdb->query(
    sprintf(
      "CREATE TABLE IF NOT EXISTS %s (
        `id` int(11) NOT NULL auto_increment,
        `slug` varchar(100) NOT NULL DEFAULT '',
        `title` varchar(100) NOT NULL,
        `image_url` text NULL,
        `image_file` text NULL,
        `description` text NULL DEFAULT NULL,
        `order` int(11) NULL,
        PRIMARY KEY  (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=latin1;",
    $wpdb->prefix.PLUGIN_TABLE
    ));
}

register_activation_hook(__FILE__, 'plugin_install');

function plugin_uninstall() {
  global $wpdb;
  $wpdb->query(
    sprintf("DROP TABLE IF EXISTS %s", $wpdb->prefix.PLUGIN_TABLE)
  );
}

register_uninstall_hook(__FILE__, 'plugin_uninstall');

/** Update entry by ID
 * @params $id, $post
 * @return bool
 **/
function updateEntryByID($post, $image) {

  global $wpdb;

  $data = array(
    'id' => $post['id'],
    'slug' => sanitize_title($post['title']),
    'title' => stripslashes($post['title']),
    'description' => stripslashes($post['description']),
    'image_url' => null,
    'image_file' => null,
    'order' => $order
    );
  // handle image uploads
  if ($image !== NULL) {
    $data['image_file'] = $image['file'];
    $data['image_url'] = $image['url'];
  }
  // update order when create method
  if ($post['id'] == null) {
    $data['order'] = returnEntryCount() + 1;
  }
  // Replace a row in a table if it exists or insert a new row in a table if the row did not already exist
  $status = $wpdb->replace($wpdb->prefix.PLUGIN_TABLE, $data);
  if ($status == false) {
    return $wpdb->show_errors();
  }
  return $status;
}

function returnEntryDetails($id) {
  global $wpdb;
  $entry = $wpdb->get_results(sprintf("SELECT * FROM `%s` WHERE id = %d", $wpdb->prefix.PLUGIN_TABLE, $id), 'ARRAY_A');
  return $entry[0];
}

function returnAllEntries() {
  global $wpdb;
  $entries = $wpdb->get_results(sprintf("SELECT * FROM `%s` ORDER BY `order` ASC", $wpdb->prefix.PLUGIN_TABLE), 'ARRAY_A');
  return $entries;
}

function returnEntryCount() {
  global $wpdb;
  $count = $wpdb->get_results(sprintf("SELECT COUNT(*) FROM `%s`", $wpdb->prefix.PLUGIN_TABLE), 'ARRAY_A');
  return (int)$count[0]["COUNT(*)"];
}

add_action('admin_menu', 'pluginMenuCreate');

/** Create options page for plugin + menu item on dashboard */
function pluginMenuCreate() {
  // add_options_page(PLUGIN_MENU, PLUGIN_MENU, 'manage_options', PLUGIN_OPTIONS, 'pluginAdminOptions');
  add_menu_page(PLUGIN_MENU, PLUGIN_MENU, 'manage_options', PLUGIN_ROUTE, 'pluginAdminOptions');
}

function render_file($filename, $vars = null) {
  if (is_array($vars) && !empty($vars)) {
    extract($vars);
  }
  ob_start();
  include $filename;
  return ob_get_clean();
}

function entry_enqueue($hook) {
  if($hook == "toplevel_page_".PLUGIN_ROUTE) {
    wp_enqueue_script( 'entry_custom_script', plugin_dir_url( __FILE__ ) . 'js/plugin-boilerplate.nativesortable.js' );
    wp_enqueue_style( 'entry_custom_style', plugin_dir_url( __FILE__ ) . 'css/plugin-boilerplate.css' );
  }
}

add_action( 'admin_enqueue_scripts', 'entry_enqueue' );

function handleUploads($files) {
  $movefile = array();
  $upload_overrides = array('test_form' => false);
  if (!function_exists('wp_handle_upload')) {
    require_once (ABSPATH . 'wp-admin/includes/file.php');
  }
  foreach ($files as $key => $value) {
  // make sure we don't overwrite a file when nothing changed
    if (strlen($files[$key]['name']) > 0) {
      $movefile[$key] = wp_handle_upload($files[$key], $upload_overrides);
      if (!$movefile || isset($movefile['error'])) {
        $message = "{$key} could not be uploaded. Please try again.";
        error_log(print_r($movefile, true));
      }
    }
  }
  return $movefile;
}

add_action( 'wp_ajax_'.PLUGIN_AJAX, 'plugin_ajax_handler' );
/**
 * $.post("<?php echo $site_url; ?>/wp-admin/admin-ajax.php", {order: getOrder(), action: 'PLUGIN_AJAX'});
 */
function plugin_ajax_handler() {
  if (!isset($_POST['order'])) {
    die('No Order Set.');
  }
  // Handle request then generate response
  global $wpdb;
  foreach ($_POST['order'] as $order => $id) {
    $id = str_replace('item-', '', $id);
    $updateQry = sprintf("UPDATE `%s` SET `order` = %d WHERE id = %d", $wpdb->prefix.PLUGIN_TABLE, $order, $id);
    $updateRes = $wpdb->query($updateQry);
    if (!$updateRes) {
      wp_send_json(array(
        'status' => false,
        'message' => $wpdb->show_errors()
        ));
      die();
    }
  }
  wp_send_json(array(
    'status' => true,
    'message' => 'Order Updated'
    ));
  die(); // avoids extra 0 at the end of the response
}

/** Displays entry  admin page */
function pluginAdminOptions() {
  if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
  }

  // message setup for errors and success strings
  $message = "";

  // prepare files that are being uploaded
  if (isset($_POST) && !empty($_POST)) {

    $files = handleUploads($_FILES);

    // Update database
    $success = updateEntryByID($_POST, $files['image'] );
    // If doesn't update, stay on edit page
    if ($success == false) {
      if ($_POST['id'] == null) {
        $_REQUEST['new'] = true;
      } else {
        $_REQUEST['edit'] = $_POST['id'];
      }
      $data = $_POST;
      $message = "The entry could not be updated. Please try again. ". $success;
    } else {
      $message = "The entry has been updated.";
    }
  }

  // default data to be sent to the templates
  $data = array(
    'site_url' => site_url(),
    'route_url' => site_url() . '/wp-admin/admin.php?page='.PLUGIN_ROUTE.'&',
    'plugin_url' => plugin_dir_url( __FILE__ ),
    'plugin_title' => PLUGIN_TITLE,
    'plugin_ajax_action' => PLUGIN_AJAX,
    'message' => $message
    );

  if (isset($_REQUEST['edit']) && $_REQUEST['edit'] !== NULL) {
    // handle editing case
    $data = array_merge($data,
      array('request' => $_REQUEST['edit']),
      returnEntryDetails((int) $_REQUEST['edit']));
    echo render_file('views/form.php', $data);

  } elseif (isset($_REQUEST['delete']) && $_REQUEST['delete'] !== NULL) {
    // handle delete function

    global $wpdb;

    $updateQry = sprintf("DELETE FROM `%s` WHERE id = %d", $wpdb->prefix.PLUGIN_TABLE, (int) $_REQUEST['delete']);
    $updateRes = $wpdb->query($updateQry);

    if ($updateRes) {
      $message = "The entry was successfully deleted.";
    } else {
      $message = "There was an error deleting the entry or the entry has already been deleted. Please try again. ". $wpdb->show_errors();
    }
    $data['message'] = $message;
    echo render_file('views/delete.php', $data);
  } elseif (isset($_REQUEST['new']) && $_REQUEST['new'] !== NULL) {
    // creation page
    $data['request'] = NULL;
    echo render_file('views/form.php', $data);
  } else {
    // index page
    $data = array_merge($data, array(
      'entries' => returnAllEntries()
      ));
    echo render_file('views/index.php', $data);
  }

}