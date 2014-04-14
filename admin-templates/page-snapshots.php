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
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab"><?php echo __('Database Export', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab nav-tab-active"><?php echo __('View Snapshots', 'lime-export'); ?></a>
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
					<option value="-1" selected="selected"><?php echo __('Bulk Actions', 'lime-export') ?></option>
					<option value="delete"><?php echo __('Delete', 'lime-export') ?></option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="<?php echo __('Apply', 'lime-export') ?>">
			</div>
		</div>

		<table class="wp-list-table widefat wple-snapshots" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" id="name" class="manage-column column-name" style=""><span><?php echo __('Filename', 'lime-export') ?></span><span class="sorting-indicator"></span></th>
					<th scope="col" id="description" class="manage-column column-description" style=""><?php echo __('Description', 'lime-export') ?></th>	
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" class="manage-column column-name" style=""><span><?php echo __('Filename', 'lime-export') ?></span><span class="sorting-indicator"></span></th>
					<th scope="col" class="manage-column column-description" style=""><?php echo __('Description', 'lime-export') ?></th>
				</tr>
			</tfoot>

			<tbody id="the-list">
				<?php if ( !empty($snapshots) ): ?>
					<?php foreach ($snapshots as $snapshot): ?>
						<tr>
							<th scope="row" class="check-column">
								<input type="checkbox" name="checked[]" value="<?php echo $snapshot['filename'] ?>" id="123">
								<label class="screen-reader-text" for="123"><?php echo $snapshot['filename'] ?></label>
							</th>

							<td class="wple-snapshot-title">
								<strong title="<?php echo date('r', $snapshot['created']) ?>"><?php echo date($date_format, $snapshot['created']) ?></strong><br/>
								<em><?php echo str_replace('.php', '.sql', $snapshot['filename']) ?></em>
								<div class="row-actions">
									<span class="deactivate"><a href="<?php echo esc_attr(wple_get_snapshot_download_url($snapshot)) ?>" title=""><?php echo __('Download', 'lime-export') ?></a> | </span>
									<span class="delete"><a href="<?php echo esc_attr(wple_get_snapshot_delete_url($snapshot)) ?>" title="" class="delete"><?php echo __('Delete', 'lime-export') ?></a></span>
								</div>
							</td>

							<td class="column-description desc">
								<div class="wple-snapshot-description">
									<p>
										<strong><?php echo __('Size', 'lime-export') ?></strong>: <?php echo wple_format_bytes($snapshot['size']); ?><br/>
										<strong><?php echo __('Tables', 'lime-export') ?></strong>: 
									</p>
									<ul>
										<?php foreach ($snapshot['tables']  as $table): ?>
											<li><?php echo $table ?></li>
										<?php endforeach ?>
									</ul>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
				<?php else: ?>
						<tr>
							<td class="column-description desc" colspan="3">
								<p><?php echo __('No snapshots found.', 'lime-export') ?></p>
							</td>
						</tr>
				<?php endif ?>
			</tbody>
		</table>
	
	</form>
</div>