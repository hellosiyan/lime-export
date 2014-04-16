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
?>
<div class="wrap">
	<div id="wple-icon-lime-export" class="icon32"><br></div>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab nav-tab-active"><?php _e('Export Database', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab"><?php _e('View Snapshots', 'lime-export'); ?></a>
	</h2>

	<form action="" method="post" id="wple-export-filters">
		<?php wp_nonce_field('wple_export','wple_export'); ?>
	
		<p>
			<label><input type="radio" name="wple_preset" value="standard" <?php echo wple_get_checked('wple_preset', 'checked="checked"', 'standard') ?> /> <?php _e('Standard export', 'lime-export'); ?></label>
			<span class="wple-description"><?php _e('structure and data, no <code>DROP TABLE</code> statement', 'lime-export'); ?></span>
		</p>
		<p>
			<label><input type="radio" name="wple_preset" value="custom" <?php echo wple_get_checked('wple_preset', '', 'custom') ?> /> <?php _e('Custom settings', 'lime-export'); ?></label>
		</p>

		<ul class="wple-export-settings">
			<li>
				<label><?php _e('Include table', 'lime-export'); ?>:</label>
				<select name="wple_dump_format">
					<?php 
					$current_format = wple_get_postval('wple_dump_format', 'both');
					foreach ($formats as $format => $label): ?>
						<option value="<?php echo esc_attr($format) ?>" <?php if($current_format==$format) echo 'selected="selected"'; ?>><?php echo esc_html($label); ?></option>
					<?php endforeach ?>
				</select>
			</li>
			<li>
				<label><?php _e('Add <code>DROP TABLE</code> statement', 'lime-export'); ?>:</label>
				<input type="checkbox" name="wple_dump_add_drop" value="1" <?php echo wple_get_checked('wple_dump_add_drop') ?>/>
			</li>
		</ul>


		<h3><?php _e('Choose which tables to export', 'lime-export') ?></h3>
		<ul class="tables-list">
			<?php foreach ($tables as $table): 
				$table = preg_replace('~^' . preg_quote($wpdb->prefix) . '~', '', $table);
				$is_standard = in_array($table, $core_tables);
				$checked = wple_get_checked('wple_export_tables', ($is_standard?'checked="checked"':''), $table);
			?>
				<li <?php if($is_standard) echo 'class="standard"'; ?>><label>
					<input type="checkbox" name="wple_export_tables[]" value="<?php echo esc_attr($table) ?>" <?php echo $checked; ?>>
					<?php echo $table; ?>
				</label></li>
			<?php endforeach ?>
			<li>
				<p>Select: <a href="#" data-action="select-all">All</a> / <a href="#" data-action="select-none">None</a> / <a href="#" data-action="select-standard">Standard</a></p>
				<input type="hidden" name="wple_export_tables[]" value="">
			</li>
		</ul>

		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button-primary" value="<?php _e('Download Export File', 'lime-export') ?>">
			<?php if ( wple_supports_snapshots() ): ?>
				<input type="submit" name="wple_save_snapshot" id="submit" class="button-secondary" value="<?php _e('Save as Snapshot', 'lime-export') ?>">
			<?php endif ?>
		</p>
	</form>
</div>