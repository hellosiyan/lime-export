<?php  

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

function wple_supports_snapshots() {
	if ( !is_writable(wple_snapshot_dir()) ) {
		return false;
	}

	if ( !wple_is_snapshot_dir_secure() ) {
		return false;
	}

	return true;
}

function wple_admin_page_snapshots() {
	global $wpdb;

	if ( !wple_supports_snapshots() ) {
		include(WPLE_PATH . '/admin-templates/page-snapshots-no-support.php');
		return;
	}

	try {
		$snapshots = wple_get_snapshots();
	} catch (WPLE_Exception $e) {
		$snapshots = array();

		wple_add_admin_notice($e->getMessage(), 'error');
		do_action('admin_notices');
	}
	
	$snapshots = array_reverse($snapshots);

	$date_format = get_option('date_format') . ' ' . get_option('time_format');

	include(WPLE_PATH . '/admin-templates/page-snapshots.php');
}

function wple_do_snapshot_download( $filename, $nice_filename ) {
	$full_snapshot_path = wple_snapshot_dir() . '/' . $filename;

	if ( !is_file($full_snapshot_path) ) {
		throw new WPLE_Exception( sprintf(
			__('The snapshot you requested is missing <code>%s</code>.', 'lime-export'), 
			str_replace(ABSPATH, '/', $full_snapshot_path)
		));
	}

	header('Content-Type: text/x-sql');
	header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
	header('Content-Disposition: attachment; filename="' . $nice_filename . '"');

    if ( isset($_SERVER['HTTP_USER_AGENT']) && preg_match('~MSIE ([0-9].[0-9]{1,2})~', $_SERVER['HTTP_USER_AGENT']) ) {
    	// IE?
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
    } else {
        header('Pragma: no-cache');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    }

	readfile($full_snapshot_path);

	return true;
}

function wple_get_snapshots() {
	$snapshots = array();
	$dir = wple_snapshot_dir() . '/';
	$csv_filename = wple_snapshot_list_file();

	$csv = fopen($csv_filename, 'r');
	if ( !$csv ) {
		throw new WPLE_Exception( sprintf(
			__('Error reading file <code>%s</code>', 'lime-export'), 
			str_replace(ABSPATH, '/', $csv_filename)
		));
	}

	while (($data = fgetcsv($csv, 1000, ",")) !== FALSE) {
		if ( count( $data) < 3 ) {
			// silently ignore invalid line
			continue;
		}
		$snapshots[] = array(
			'filename' => $data[0],
			'tables' => explode('|', $data[1]),
			'created' => intval($data[2]),
			'size' => intval($data[3]),
		);
	}

	fclose($csv);
	return $snapshots;
}

function wple_add_snapshot( $filename, $tables, $time = null ) {
	$dir = wple_snapshot_dir() . '/';

	wple_add_multiple_snapshots(array(
		array(
			'filename' => $filename,
			'tables' => $tables,
			'created' => intval(!$time ? time(): $time),
			'size' => filesize($dir . $filename),
		)
	));
}


function wple_add_multiple_snapshots( $snapshots ) {
	$csv_filename = wple_snapshot_list_file();

	$csv = fopen($csv_filename, 'a');
	if ( !$csv ) {
		throw new WPLE_Exception( sprintf(
			__('Cannot open file <code>%s</code>', 'lime-export'), 
			str_replace(ABSPATH, '/', $csv_filename)
		));
	}

	foreach ($snapshots as $snapshot) {
		fputcsv($csv, array(
			$snapshot['filename'],
			implode('|', $snapshot['tables']),
			$snapshot['created'],
			$snapshot['size']
		));
	}
	
	fclose($csv);
}

function wple_remove_snapshot( $filename ) {
	$snapshots = wple_get_snapshots();

	$dir = wple_snapshot_dir() . '/';
	$csv_filename = wple_snapshot_list_file();

	$csv = fopen($csv_filename, 'w');
	if ( !$csv ) {
		throw new WPLE_Exception( sprintf(
			__('Cannot open file <code>%s</code>', 'lime-export'), 
			str_replace(ABSPATH, '/', $csv_filename)
		));
	}

	// Put back header
	fwrite($csv, "<?php exit(); ?>\n");

	foreach ($snapshots as $snapshot) {
		if ( $snapshot['filename'] == $filename ) {
			if ( is_file( $dir . $snapshot['filename'] ) ) {
				unlink( $dir . $snapshot['filename'] );
			}
		} else {
			$snapshot['tables'] = implode('|', $snapshot['tables']);
			fputcsv($csv, $snapshot);
		}
	}
	fclose($csv);
}

function wple_get_snapshot_delete_url($snapshot) {
	$bare_url = add_query_arg(array('wple-delete' => $snapshot['filename']));

	$complete_url = wp_nonce_url( $bare_url, 'wple_delete-snapshot_' . $snapshot['filename'] );

	return $complete_url;
}

function wple_get_snapshot_download_url($snapshot) {
	$bare_url = add_query_arg(array('wple-download' => $snapshot['filename']));

	$complete_url = wp_nonce_url( $bare_url, 'wple_download-snapshot_' . $snapshot['filename'] );

	return $complete_url;
}

function wple_snapshot_dir() {
	$upload_dir = wp_upload_dir();
	$upload_dir = $upload_dir['basedir'] . '/wple-snapshots';
	
	return $upload_dir;
}

function wple_snapshot_list_file() {
	return wple_snapshot_dir() . '/list.php';
}

function wple_create_snapshot_dir() {
	$upload_dir = wple_snapshot_dir();

	if ( !is_dir( $upload_dir ) ) {
		$success = @mkdir($upload_dir, 0750, true);
		if ( !$success ) {
			throw new WPLE_Exception( sprintf(
				__('Unable to create directory <code>%s</code>', 'lime-export'),
				str_replace(ABSPATH, '/', $upload_dir)
			));
		}
	}

	$list_filename = wple_snapshot_list_file();
	if ( !file_exists($list_filename) ) {
		file_put_contents($list_filename, "<?php exit(); ?>\n");
		chmod($list_filename, 0640);

		// Upgrading from 0.4 ?
		wple_upgrade();
	}
	
	$index_filename = $upload_dir . '/index.html';
	if ( !file_exists($index_filename) ) {
		touch($index_filename);
		chmod($index_filename, 0640);
	}
}

function wple_is_snapshot_dir_secure() {
	$dir = wple_snapshot_dir();
	$dir_perms = fileperms($dir);

	if ( ($dir_perms & 0x0007) != 0 ) {
		return false;
	}

	if ( !file_exists($dir . '/index.html') ) {
		return false;
	}

	return true;
}