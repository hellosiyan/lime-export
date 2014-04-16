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
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab"><?php _e('Database Export', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab nav-tab-active"><?php _e('View Snapshots', 'lime-export'); ?></a>
	</h2>

	<form action="" method="post">
		<?php wp_nonce_field('wple_snapshot-action','wple_snapshot-action'); ?>

		<p><?php 
		printf(
			__('Snapshot files are located in <code>%s</code>.', 'lime-export'), 
			str_replace(ABSPATH, '/', wple_snapshot_dir())
		);
		?></p>

		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="wple-action">
					<option value="-1" selected="selected"><?php _e('Bulk Actions', 'lime-export') ?></option>
					<option value="delete"><?php _e('Delete', 'lime-export') ?></option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="<?php _e('Apply', 'lime-export') ?>">
			</div>
		</div>

		<table class="wp-list-table widefat wple-snapshots" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" id="name" class="manage-column column-name" style=""><span><?php _e('Filename', 'lime-export') ?></span><span class="sorting-indicator"></span></th>
					<th scope="col" id="description" class="manage-column column-description" style=""><?php _e('Description', 'lime-export') ?></th>	
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" class="manage-column column-name" style=""><span><?php _e('Filename', 'lime-export') ?></span><span class="sorting-indicator"></span></th>
					<th scope="col" class="manage-column column-description" style=""><?php _e('Description', 'lime-export') ?></th>
				</tr>
			</tfoot>

			<tbody id="the-list">
				<?php if ( !empty($snapshots) ): ?>
					<?php foreach ($snapshots as $snapshot): ?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="checked[]" value="<?php echo esc_attr($snapshot['filename']) ?>" id="123">
								<label class="screen-reader-text" for="123"><?php echo esc_html($snapshot['filename']) ?></label>
							</th>

							<td class="wple-snapshot-title">
								<strong title="<?php echo date('r', $snapshot['created']) ?>"><?php echo date($date_format, $snapshot['created']) ?></strong><br/>
								<em><?php echo esc_html(str_replace('.php', '.sql', $snapshot['filename'])) ?></em>
								<div class="row-actions">
									<span class="deactivate"><a href="<?php echo esc_attr(wple_get_snapshot_download_url($snapshot)) ?>" title=""><?php _e('Download', 'lime-export') ?></a> | </span>
									<span class="delete"><a href="<?php echo esc_attr(wple_get_snapshot_delete_url($snapshot)) ?>" title="" class="delete"><?php _e('Delete', 'lime-export') ?></a></span>
								</div>
							</td>

							<td class="column-description desc">
								<div class="wple-snapshot-description">
									<p>
										<strong><?php _e('Size', 'lime-export') ?></strong>: <?php echo esc_html(wple_format_bytes($snapshot['size'])); ?><br/>
										<strong><?php _e('Tables', 'lime-export') ?></strong>: 
									</p>
									<ul>
										<?php foreach ($snapshot['tables']  as $table): ?>
											<li><?php echo esc_html($table) ?></li>
										<?php endforeach ?>
									</ul>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
						<tr>
							<td class="column-description desc" colspan="3">
								<p><?php _e('No snapshots found.', 'lime-export') ?></p>
							</td>
						</tr>
				<?php endif ?>
			</tbody>
		</table>
	
	</form>
</div>