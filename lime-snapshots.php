<?php  

function wple_get_snapshots() {
	$snapshots = array();
	$dir = wple_snapshot_dir() . '/';
	$csv = fopen($dir . 'list.csv', 'r');
	if ( !$csv ) {
		throw new WPLE_Exception(WPLE_MSG_FILE_READ_ERROR);
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
			'size' => wple_format_bytes(intval($data[3])),
		);
	}

	fclose($csv);
	return $snapshots;
}

function wple_add_snapshot( $filename, $tables, $time = null ) {
	$time = !$time ? time(): $time;

	$dir = wple_snapshot_dir() . '/';
	$csv = fopen($dir . 'list.csv', 'a');
	if ( !$csv ) {
		throw new WPLE_Exception(WPLE_MSG_FILE_CREAT_ERROR);
	}

	fputcsv($csv, array($filename, implode('|', $tables), $time, filesize($dir . $filename)) );
	fclose($csv);
}

function wple_remove_snapshot( $filename ) {
	$snapshots = wple_get_snapshots();

	$dir = wple_snapshot_dir() . '/';
	$csv = fopen($dir . 'list.csv', 'w');
	if ( !$csv ) {
		throw new WPLE_Exception(WPLE_MSG_FILE_CREAT_ERROR);
	}

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






