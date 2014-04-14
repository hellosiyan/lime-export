<?php  

// Block direct includes
if ( !defined('WPINC') ) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

function wple_upgrade_to_1_0() {
	$upload_dir = wple_snapshot_dir();
	$list_filename = wple_snapshot_list_file();
	$old_list_filename = $upload_dir . '/list.csv';

	/* Change directory and file permissions */
	if ( is_dir($upload_dir) ) {
		chmod($upload_dir, 0750);
		
		$dir_handle = opendir($upload_dir);
		if ( $dir_handle ) {
		    while (false !== ($entry_filename = readdir($dir_handle))) {
		    	if ( strpos($entry_filename, '.php') === FALSE) {
		    		continue;
		    	}

		    	chmod($upload_dir . '/' . $entry_filename, 0640);
		    }

		    closedir($dir_handle);
		}
	}


	/* Migrate old list.csv file */
	if ( file_exists($old_list_filename) ) {
		$old_snapshots = array();

		$csv = fopen($old_list_filename, 'r');

		while (($data = fgetcsv($csv)) !== FALSE) {
			if ( count( $data) < 3 || !file_exists($upload_dir . '/' . $data[0]) ) {
				continue;
			}

			$old_snapshots[] = array(
				'filename' => $data[0],
				'tables' => explode('|', $data[1]),
				'created' => intval($data[2]),
				'size' => intval($data[3]),
			);
		}
		fclose($csv);

		wple_create_snapshot_dir();
		wple_add_multiple_snapshots($old_snapshots);
		
		unlink($old_list_filename);
	}
	
	return;
}