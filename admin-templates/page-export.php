<?php 
// Block direct includes
if ( !defined('WPINC') ) {
	header("HTTP/1.0 404 Not Found");
	exit;
}
?>
<div class="wrap">
	<div id="wple-icon-lime-export" class="icon32"><br></div>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab nav-tab-active"><?php echo __('Export Database', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab"><?php echo __('View Snapshots', 'lime-export'); ?></a>
	</h2>

	<form action="" method="post" id="wple-export-filters">
		<?php wp_nonce_field('wple_export','wple_export'); ?>
	
		<p>
			<label><input type="radio" name="wple_preset" value="standard" <?php echo wple_get_checked('wple_preset', 'checked="checked"', 'standard') ?> /> <?php echo __('Standard export', 'lime-export'); ?></label>
			<span class="wple-description"><?php echo __('structure and data, no <code>DROP TABLE</code> statement', 'lime-export'); ?></span>
		</p>
		<p>
			<label><input type="radio" name="wple_preset" value="custom" <?php echo wple_get_checked('wple_preset', '', 'custom') ?> /> <?php echo __('Custom settings', 'lime-export'); ?></label>
		</p>

		<ul class="wple-export-settings">
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
			<?php if ( wple_supports_snapshots() ): ?>
				<input type="submit" name="wple_save_snapshot" id="submit" class="button-secondary" value="<?php echo __('Save as Snapshot', 'lime-export') ?>">
			<?php endif ?>
		</p>
	</form>
</div>