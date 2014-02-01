<div class="wrap">
	<div id="icon-lime-export" class="icon32"><br></div>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab nav-tab-active"><?php echo __('Database Export', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab"><?php echo __('View Snapshots', 'lime-export'); ?></a>
	</h2>

	<?php 
	if ( isset($_GET['message']) ) {
		switch ( $_GET['message'] ) {
			case WPLE_MSG_NO_SELECTION:
				$msg = __('No tables selected for export.', 'lime-export');
				break;
			case WPLE_MSG_NO_SPACE:
				$msg = __('Insufficient space to save the file.', 'lime-export');
				break;
			case WPLE_MSG_FILE_CREAT_ERROR:
				$msg = __('Error creating file.');
				break;
			case WPLE_MSG_NOT_APACHE:
				$msg = __('This feature requires Apache Web Server.', 'lime-export');
				break;
			default:
				if ( is_string( $_GET['message'] ) ) {
					$msg = strip_tags($_GET['message'], '<br><code><em><strong>');
				}
		}

		if ( !empty($msg) ) {
			echo '<div class="updated"><p><strong>' . $msg . '</strong></p></div>';
		}
	}

	?>

	<form action="" method="post" id="export-filters">
		<?php wp_nonce_field('wple_download','wple_download'); ?>
	
		<p>
			<label><input type="radio" name="wple_preset" value="standard" <?php echo wple_get_checked('wple_preset', 'checked="checked"', 'standard') ?> /> <?php echo __('Standard export', 'lime-export'); ?></label>
			<span class="description"><?php echo __('structure and data, no <code>DROP TABLE</code> statement', 'lime-export'); ?></span>
		</p>
		<p>
			<label><input type="radio" name="wple_preset" value="custom" <?php echo wple_get_checked('wple_preset', '', 'custom') ?> /> <?php echo __('Custom settings', 'lime-export'); ?></label>
		</p>

		<ul class="export-settings">
			<li>
				<label><?php echo __('Include table', 'lime-export'); ?>:</label>
				<select name="wple_dump_format">
					<?php 
					$current_format = wple_get_postval('wple_dump_format', 'both');
					foreach ($formats as $format => $label): ?>
						<option value="<?php echo $format ?>" <?php if($current_format==$format) echo 'selected="selected"'; ?>><?php echo $label; ?></option>
					<?php endforeach ?>
				</select>
			</li>
			<li>
				<label><?php echo __('Add <code>DROP TABLE</code> statement', 'lime-export'); ?>:</label>
				<input type="checkbox" name="wple_dump_add_drop" value="1" <?php echo wple_get_checked('wple_dump_add_drop') ?>/>
			</li>
		</ul>


		<h3><?php echo __('Choose which tables to export', 'lime-export') ?></h3>
		<ul class="tables-list">
			<?php foreach ($tables as $table): 
				$table = preg_replace('~^' . preg_quote($wpdb->prefix) . '~', '', $table);
				$is_standard = in_array($table, $core_tables);
				$checked = wple_get_checked('wple_export_tables', ($is_standard?'checked="checked"':''), $table);
			?>
				<li <?php if($is_standard) echo 'class="standard"'; ?>><label>
					<input type="checkbox" name="wple_export_tables[]" value="<?php echo $table ?>" <?php echo $checked; ?>>
					<?php echo $table; ?>
				</label></li>
			<?php endforeach ?>
			<li>
				<p>Select: <a href="#" data-action="select-all">All</a> / <a href="#" data-action="select-none">None</a> / <a href="#" data-action="select-standard">Standard</a></p>
				<input type="hidden" name="wple_export_tables[]" value="">
			</li>
		</ul>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php echo __('Download Export File', 'lime-export') ?>">
			<input type="submit" name="wple_save_snapshot" id="submit" class="button-secondary" value="<?php echo __('Save as Snapshot', 'lime-export') ?>">
		</p>
	</form>
</div>