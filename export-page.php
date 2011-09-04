<div class="wrap">
	<div id="icon-lime-export" class="icon32"><br></div><h2><?php echo __('Database Export'); ?></h2>
	<?php 
	if ( isset($_GET['message']) ) {
		switch ( $_GET['message'] ) {
			case WPLE_MSG_NO_SELECTION:
				$msg = __('No tables selected for export.');
				break;
			case WPLE_MSG_NO_SPACE:
				$msg = __('Insufficient space to save the temp file.');
				break;
			case WPLE_MSG_TMPFILE_ERROR:
				$msg = __('Error creating temporary file.');
				break;
		}

		if ( !empty($msg) ) {
			echo '<div class="updated"><p><strong>' . $msg . '</strong></p></div>';
		}
	}

	?>

	<h3><?php echo __('Choose which tables to export') ?></h3>
	<form action="" method="post" id="export-filters">
		<?php wp_nonce_field('wple_download','wple_download'); ?>

		<ul>
			<?php foreach ($tables as $table): 
				$table = preg_replace('~^' . preg_quote($wpdb->prefix) . '~', '', $table);
				$checked = in_array($table, $wpdb->tables) || in_array($table, $wpdb->global_tables) || in_array($table, $wpdb->ms_global_tables);
			?>
				<li><label>
					<input type="checkbox" name="wple_export_tables[]" value="<?php echo $table ?>" <?php if($checked) echo 'checked="checked"'; ?>>
					<?php echo $table; ?>
				</label></li>
			<?php endforeach ?>
		</ul>

		<p class="submit"><input type="submit" name="submit" id="submit" class="button-secondary" value="<?php echo __('Download Export File') ?>"></p>
	</form>
</div>