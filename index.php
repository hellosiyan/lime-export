<?php
/*
Plugin Name: Lime Export
Plugin URI: https://github.com/xsisqox/lime-export
Description: Advanced Database export utility
Version: 0.4
Author: Siyan Panayotov
License: GPLv2 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Block direct includes
if ( !defined('WPINC') ) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

define('WPLE_VERSION', '0.4');

define('WPLE_PATH', dirname(__FILE__));
define('WPLE_URL', WP_PLUGIN_URL . '/' . basename(WPLE_PATH) );

define('WPLE_MAX_QUERY_SIZE', 50000);

include_once('lib/helpers.php');
include_once('lib/lime-snapshots.php');

add_action( 'init', 'wple_init' );

function wple_init() {
	# Create required directory structure
	try {
		wple_create_snapshot_dir();
	} catch (WPLE_Exception $e) {
		wple_add_admin_notice($e->getMessage());
	}

	# Register hooks
	add_action( 'admin_notices', 'wple_show_notices' );
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

function wple_show_notices() {
	$notices = wple_get_admin_notices();

	if ( empty($notices) ) {
		return;
	}

	echo '<div id="message" class="updated fade"><p><strong>Database Export:</strong> ' . implode('</p><p>', $notices) . '</p></div>';
}

function wple_admin_init() {
	try {
		if ( isset($_POST['wple_export']) ) {
			wple_admin_handle_export();
		} elseif ( isset($_GET['wple-download']) ) {
			wple_admin_handle_snapshot_download($_GET['wple-download']);
			exit();
		} elseif ( isset($_GET['wple-delete']) ) {
			wple_admin_handle_snapshot_delete($_GET['wple-delete']);
			wp_redirect( remove_query_arg('wple-delete') );
		} elseif( isset($_POST['wple-action']) && $_POST['wple-action'] == 'delete' ) {
			wple_admin_handle_snapshot_delete_bulk();
			wp_redirect( add_query_arg() );
		}
	} catch (WPLE_Exception $e) {
		wple_add_admin_notice($e->getMessage());
	}

	wp_enqueue_style('lemon-export-style', WPLE_URL . '/assets/style.css');
	wp_enqueue_script('lemon-export-script', WPLE_URL . '/assets/functions.js');
}

function wple_admin_handle_export() {
	check_admin_referer( 'wple_export', 'wple_export' );
	wple_do_export();
}

function wple_admin_handle_snapshot_download($snapshot_filename) {
	check_admin_referer( 'wple_download-snapshot_' . $snapshot_filename );

	$snapshots = wple_get_snapshots();

	foreach ($snapshots as $snapshot) {
		if ( $snapshot_filename == $snapshot['filename'] ) {
			wple_do_snapshot_download($snapshot['filename'], 'dump_' . date('Ymd_His', $snapshot['created']) . '.sql');
		}
	}

	throw new WPLE_Exception( __('The snapshot you requested is missing', 'lime-export') );
}

function wple_admin_handle_snapshot_delete($snapshot_filename) {
	check_admin_referer( 'wple_delete-snapshot_' . $snapshot_filename );

	$snapshots = wple_get_snapshots();

	foreach ($snapshots as $snapshot) {
		if ( $snapshot_filename == $snapshot['filename'] ) {
			wple_remove_snapshot($snapshot['filename']);
			return;
		}
	}

	throw new WPLE_Exception( __('The snapshot you requested is missing', 'lime-export') );
}

function wple_admin_handle_snapshot_delete_bulk() {
	check_admin_referer( 'wple_snapshot-action', 'wple_snapshot-action' );

	if ( empty( $_POST['checked'] ) ) {
		throw new WPLE_Exception( __('No snapshots selected', 'lime-export') );
	}

	$snapshots = wple_get_snapshots();

	foreach ($snapshots as $snapshot) {
		if ( in_array($snapshot['filename'], $_POST['checked']) ) {
			wple_remove_snapshot($snapshot['filename']);
		}
	}
}

function wple_admin_page_export() {
	global $wpdb;

	$tables = wple_get_existing_tables();
	$core_tables = array_merge($wpdb->tables, $wpdb->global_tables, $wpdb->ms_global_tables);
	$formats = array(
		'both' => __('Structure and Data', 'lime-export'),
		'structure' => __('Structure', 'lime-export'),
		'data' => __('Data', 'lime-export'),
	);

	include(WPLE_PATH . '/admin-templates/page-export.php');
}

function wple_do_export() {
	global $wpdb, $wple_export_file, $wple_time_start;

	if ( empty($_POST['wple_export_tables']) ) {
		throw new WPLE_Exception( __('No tables selected for export', 'lime-export') );
	}

	$config = wple_export_config();
	$nice_filename = 'dump_' . date('Ymd_His') . '.sql';

	$filename = md5(date('r')) . '.php';

	$export_tables = $_POST['wple_export_tables'];
	$existing_tables = wple_get_existing_tables();

	foreach ($export_tables as $index => $table_al) {
		if ( !in_array($wpdb->prefix . $table_al, $existing_tables) ) {
			array_splice($export_tables, $index, 1);
		}
	}

	if ( empty($export_tables) ) {
		throw new WPLE_Exception( __('No tables selected for export', 'lime-export') );
	}

	$wple_export_file = tmpfile();
	$wple_time_start = time();
	$table_separator = "\n-- --------------------------------------------------------\n\n";

	if ( !$wple_export_file ) {
		throw new WPLE_Exception( __('Error creating export file', 'lime-export') );
	}

	$head = "-- <?php exit(); ?>\n" .
	 		"-- Lime Export SQL Dump\n" .
			"-- version " . WPLE_VERSION . "\n" . 
			"--\n" .
			"-- Host: " . $wpdb->dbhost . "\n" .
			"-- Database: " . $wpdb->dbname . "\n" . 
			"-- Generation Time: " . date('r') . "\n\n";


    $head .= "SET time_zone = \"+00:00\";\n";

    $old_tz = $wpdb->get_var('SELECT @@session.time_zone');
    $wpdb->query('SET time_zone = "+00:00"');

	wple_output_handler($head);
	foreach ($export_tables as $table_al) {
		$table = $wpdb->prefix . $table_al;

		$query = 'SELECT * FROM `' . $table . '` ';

		wple_output_handler($table_separator);

		if ( $config['format'] == 'both' || $config['format'] == 'structure' ) {
			wple_export_structure($table, $config);
		}

		if ( $config['format'] == 'both' || $config['format'] == 'data' ) {
			wple_export_data($table, $query, $config);
		}
	}

	$wpdb->query('SET time_zone = "' . $old_tz . '"');

	if ( isset( $_POST['wple_save_snapshot'] ) ) {
		wple_do_export_snapshot($filename, $export_tables);
		fclose($wple_export_file);
	} else {
		wple_do_export_download($nice_filename);
		exit();	
	}
}

function wple_do_export_download( $filename ) {
	global $wple_export_file;

	if ( !$wple_export_file ) {
		return false;
	}

	header('Content-Type: text/x-sql');
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Disposition: attachment; filename="' . $filename . '"');

    if ( isset($_SERVER['HTTP_USER_AGENT']) && preg_match('~MSIE ([0-9].[0-9]{1,2})~', $_SERVER['HTTP_USER_AGENT']) ) {
    	// IE?
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    }

	fflush($wple_export_file);
	rewind($wple_export_file);
	fpassthru($wple_export_file);

	return true;
}

function wple_do_export_snapshot( $filename, $export_tables ) {
	global $wple_export_file;

	$dir = wple_snapshot_dir() . '/';
	$basename = preg_replace('~\.php$~', '', $filename);
	$copy_counter = 1;
	while ( is_file($dir . $filename) ) {
		$filename = $basename . '_(' . $copy_counter . ').php';
		$copy_counter ++;
	}

	$snaphot_file = fopen($dir . $filename, 'w');
	if ( !$snaphot_file ) {
		throw new WPLE_Exception( sprintf(
			__('Cannot open file <code>%s</code>', 'lime-export'), 
			$dir . $filename
		));
	}

	fflush($wple_export_file);
	rewind($wple_export_file);
	stream_copy_to_stream($wple_export_file, $snaphot_file);
	fclose($snaphot_file);

	wple_add_snapshot($filename, $export_tables);

	wple_add_admin_notice( sprintf(
		__('Created snapshot <code>%s</code>', 'lime-export'), 
		str_replace('.php', '.sql', $filename)
	));

	return $filename;
}

function wple_export_structure($table, $config) {
	global $wpdb;

	$schema_create = wple_export_comment( sprintf(__('Table structure for table %s', 'lime-export'), $table)) . "\n";
    $auto_increment = '';

    if ( $config['add_drop_table'] ) {
    	$schema_create .= "DROP TABLE IF EXISTS `" . $table . "`;\n";
    }

    // Table status
    $result = mysql_query('SHOW TABLE STATUS FROM `' . $wpdb->dbname . '` LIKE \'' . wple_addslashes($table) . '\'', $wpdb->dbh);
    if ($result != FALSE) {
        if (mysql_num_rows($result) > 0) {
            $tmpres = mysql_fetch_array($result, MYSQL_ASSOC);
            // Here we optionally add the AUTO_INCREMENT next value,
            // but starting with MySQL 5.0.24, the clause is already included
            // in SHOW CREATE TABLE so we'll remove it below
            if ( !empty($tmpres['Auto_increment']) ) {
                $auto_increment .= ' AUTO_INCREMENT=' . $tmpres['Auto_increment'] . ' ';
            }
        }
        mysql_free_result($result);
    }


    // Table structure
	$result = mysql_query('SHOW CREATE TABLE `' . $table . '` ', $wpdb->dbh);


    if ($result != FALSE && ($row = mysql_fetch_array($result, MYSQL_NUM))) {
        $create_query = $row[1];
        unset($row);

		// Convert end of line chars to one that we want (note that MySQL doesn't return query it will accept in all cases)
		if (strpos($create_query, "(\r\n ")) {
			$create_query = str_replace("\r\n", "\n", $create_query);
		} elseif (strpos($create_query, "(\r ")) {
			$create_query = str_replace("\r", "\n", $create_query);
		}

		$create_query = preg_replace('/^CREATE TABLE/', 'CREATE TABLE IF NOT EXISTS', $create_query);
		$schema_create .= $create_query;
    }

	$schema_create = preg_replace('/AUTO_INCREMENT\s*=\s*([0-9])+/', '', $schema_create);

	$schema_create .= $auto_increment . ";\n";

	wple_output_handler($schema_create);

    mysql_free_result($result);
}

function wple_export_data($table, $sql_query, $config) {
	global $wpdb;
	$i = 0; 
	$j = 0;

	$result = mysql_query($sql_query, $wpdb->dbh);
	$fields_cnt = mysql_num_fields($result);
	$meta = array();
	$flags = array();

    $search = array("\x00", "\x0a", "\x0d", "\x1a"); //\x08\\x09, not required
    $replace = array('\0', '\n', '\r', '\Z');

	$query_size = 0;
	$current_row = 0;
	$field_set = array();

	while ( $i < @mysql_num_fields( $result ) ) {
		$meta[$i] = @mysql_fetch_field( $result );
		$flags[$i] = mysql_field_flags($result, $i);
		$field_set[$i] = $meta[$i]->name;
		$i++;
	}

	$schema_insert = "\n" . wple_export_comment( sprintf(__('Dumping data for table %s', 'lime-export'), $table) ) . "\n";
	$schema_insert .= "INSERT INTO `" . $table . "` (`" . implode('`, `', $field_set) . "`) VALUES\n";

	while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
		$values = array();
		$current_row++;

		for ($j=0; $j < $fields_cnt; $j++) { 
            if (!isset($row[$j]) || is_null($row[$j])) {
                $values[] = 'NULL';
            } elseif ($meta[$j]->numeric && $meta[$j]->type != 'timestamp' && ! $meta[$j]->blob) {
	            // a number
	            // timestamp is numeric on some MySQL 4.1, BLOBs are sometimes numeric
                $values[] = $row[$j];
            } elseif (stristr($flags[$j], 'BINARY') && $meta[$j]->blob && isset($GLOBALS['sql_hex_for_blob'])) {
	            // a true BLOB
                if (empty($row[$j]) && $row[$j] != '0') {
                	// empty blobs need to be different, but '0' is also empty :-(
                    $values[] = '\'\'';
                } else {
                    $values[] = '0x' . bin2hex($row[$j]);
                }
            } elseif ($meta[$j]->type == 'bit') {
            	// detection of 'bit' works only on mysqli extension
                $values[] = "b'" . wple_addslashes(wple_escape_bit($row[$j], $meta[$j]->length)) . "'";
            } else {
            	// something else -> treat as a string
                $values[] = '\'' . str_replace($search, $replace, wple_addslashes($row[$j])) . '\'';
            }
		}

        if ($current_row == 1) {
            $insert_line  = $schema_insert . '(' . implode(', ', $values) . ')';
        } else {
            $insert_line  = '(' . implode(', ', $values) . ')';
            if ( WPLE_MAX_QUERY_SIZE > 0 && $query_size + strlen($insert_line) > WPLE_MAX_QUERY_SIZE) {
                wple_output_handler(";\n");
                $query_size = 0;
                $current_row = 1;
                $insert_line = $schema_insert . $insert_line;
            }
        }
        $query_size += strlen($insert_line);
            
        unset($values);

        wple_output_handler(($current_row == 1 ? '' : ",\n") . $insert_line);
	}

    mysql_free_result($result);

	if ($current_row > 0) {
	    wple_output_handler(";\n");
	}

}

function wple_output_handler( $line ) {
	global $wple_export_file, $wple_time_start;
	
    $write_result = @fwrite($wple_export_file, $line);
    if ( !$write_result || ($write_result != strlen($line))) {
    	throw new WPLE_Exception( __('Insufficient space to save the file.', 'lime-export') );
    }

    $time_now = time();

    if ($wple_time_start >= $time_now + 30) {
        $wple_time_start = $time_now;
        header('X-pmaPing: Pong');
    }
}

function wple_export_comment( $text ) {
	return (empty($text) ? '' : "--\n-- ") . $text . "\n--\n";
}

function wple_export_config() {
	$preset = isset( $_POST['wple_preset'] ) ? $_POST['wple_preset']: 'standard';

	$config = array(
		'file_name' => '@DATABASE@.@DATE@',
		'format' => 'both',
		'add_drop_table' => false,
	);

	if ( $preset != 'custom' ) {
		return $config;
	}

	if ( isset($_POST['wple_dump_format']) ) {
		$config['format'] = in_array( $_POST['wple_dump_format'], array('both', 'structure', 'data') ) ? $_POST['wple_dump_format']: 'both';
	}

	if ( isset($_POST['wple_dump_add_drop']) ) {
		$config['add_drop_table'] = true;
	} else {
		$config['add_drop_table'] = false;
	}

	return $config;
}
