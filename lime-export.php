<?php
/*
Plugin Name: Lime Export
Plugin URI: https://github.com/xsisqox/lime-export
Description: Advanced Database export utility
Version: 1.0
Author: Siyan Panayotov
License: GPL2
*/

/*
Copyright (C) 2011-2014 Siyan Panayotov <siyan.panayotov@gmail.com>

This file is part of Lime Export.

Lime Export is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

Lime Export is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Lime Export; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Block direct includes
if ( !defined('WPINC') ) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

define('WPLE_VERSION', '1.0');

add_action( 'init', 'wple_init' );

register_activation_hook( __FILE__, 'wple_upgrade' );

function wple_init() {
	# Register hooks
	add_action( 'admin_menu', 'wple_register_pages' );
	add_action( 'load-tools_page_lime-export', 'wple_admin_init' );
	add_action( 'load-tools_page_lime-snapshots', 'wple_admin_init' );
}

function wple_register_pages() {
	if ( isset($_GET['page']) && $_GET['page'] == 'lime-snapshots' ) {
		$menu_slug = 'lime-snapshots';
		$render_function = 'wple_admin_page_snapshots';
	} else {
		$menu_slug = 'lime-export';
		$render_function = 'wple_admin_page_export';
	}

	add_submenu_page('tools.php', __('Database Export', 'lime-export'), __('Database Export', 'lime-export'), 'export', $menu_slug, $render_function);
}

function wple_admin_init() {
	wple_load();

	try {
		wple_admin_handle_export();

		wple_create_snapshot_dir();

		if ( wple_supports_snapshots() ) {
			wple_admin_handle_snapshot_download();
			wple_admin_handle_snapshot_delete();
			wple_admin_handle_snapshot_delete_bulk();
		}
	} catch (WPLE_Exception $e) {
		wple_add_admin_notice($e->getMessage(), 'error');
	}

	if ( !wple_supports_snapshots() ) {
		wple_add_admin_notice(__('Snapshots are not supported.', 'lime-export'));
	}

	wp_enqueue_style('lemon-export-style', WPLE_URL . '/assets/style.css', array(), WPLE_VERSION);
	wp_enqueue_script('lemon-export-script', WPLE_URL . '/assets/functions.js', array(), WPLE_VERSION);
}

function wple_upgrade() {
	if ( defined('WPLE_UPGRADING') ) {
		return;
	}

	define('WPLE_UPGRADING', true);

	wple_load();
	include_once(WPLE_PATH . '/upgrade/lime-export-1.0.php');

	wple_upgrade_to_1_0();
}


function wple_load() {
	if ( !defined('WP_ADMIN') || defined('WPLE_PATH') ) {
		return;
	}

	define('WPLE_PATH', dirname(__FILE__));
	define('WPLE_URL', WP_PLUGIN_URL . '/' . basename(WPLE_PATH) );

	define('WPLE_MAX_QUERY_SIZE', 50000);

	include_once(WPLE_PATH . '/lib/mysql-alias.php');
	include_once(WPLE_PATH . '/lib/helpers.php');
	include_once(WPLE_PATH . '/lib/lime-export.php');
	include_once(WPLE_PATH . '/lib/lime-snapshots.php');

	add_action( 'admin_notices', 'wple_show_notices' );
}