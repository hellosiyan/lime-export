<div class="wrap">
	<div id="icon-lime-export" class="icon32"><br></div>
	<h2 class="nav-tab-wrapper">
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab"><?php echo __('Database Export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab nav-tab-active"><?php echo __('View Snapshots'); ?></a>
	</h2>

	<?php 
	if ( isset($_GET['message']) ) {
		switch ( $_GET['message'] ) {
			case WPLE_MSG_NO_SELECTION:
				$msg = __('Nothing selected.');
				break;
			case WPLE_MSG_NO_SPACE:
				$msg = __('Insufficient space to save the file.');
				break;
			case WPLE_MSG_FILE_CREAT_ERROR:
				$msg = __('Error creating file.');
				break;
			case WPLE_MSG_FILE_READ_ERROR:
				$msg = __('Error reading file.');
				break;
			case WPLE_MSG_NOT_APACHE:
				$msg = __('This feature requires Apache Web Server.');
				break;
			case WPLE_MSG_SNAPSHOT_NOT_FOUND:
				$msg = __('The snapshot you requested is missing.');
		}

		if ( !empty($msg) ) {
			echo '<div class="updated"><p><strong>' . $msg . '</strong></p></div>';
		}
	}

	?>

	<form action="" method="post">
		<?php wp_nonce_field('wple_snapshot','wple_snapshot'); ?>

		<p><?php 
		$public_dir = wp_upload_dir();
		$public_dir = $public_dir['baseurl'] . '/wple-snapshots/';
		printf(__('Snapshot files are located in <code>%s</code>.<br/>Please ensure the directory is not accessible <a href="%s">from the web</a>.'), wple_snapshot_dir(), $public_dir);
		?></p>

		<div class="tablenav top">
			<div class="alignleft actions">
				<select name="action">
					<option value="-1" selected="selected"><?php echo __('Bulk Actions') ?></option>
					<option value="delete"><?php echo __('Delete') ?></option>
				</select>
				<input type="submit" name="" id="doaction" class="button-secondary action" value="<?php echo __('Apply') ?>">
			</div>
		</div>

		<table class="wp-list-table widefat wple-snapshots" cellspacing="0">
			<thead>
				<tr>
					<th scope="col" id="cb" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" id="name" class="manage-column column-name" style=""><?php echo __('Filename') ?></th>
					<th scope="col" id="description" class="manage-column column-description" style=""><span><?php echo __('Description') ?></span><span class="sorting-indicator"></span></th>	
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column" style="">
						<input type="checkbox" />
					</th>
					<th scope="col" class="manage-column column-name" style=""><?php echo __('Filename') ?></th>
					<th scope="col" class="manage-column column-description" style=""><span><?php echo __('Description') ?></span><span class="sorting-indicator"></span></th>
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

							<td class="snapshot-title">
								<strong><?php echo $snapshot['filename'] ?></strong>
								<div class="row-actions-visible">
								<span class="deactivate"><a href="<?php echo add_query_arg('download', $snapshot['filename']) ?>" title=""><?php echo __('Download') ?></a> | </span>
								<span class="delete"><a href="<?php echo add_query_arg('delete', $snapshot['filename']) ?>" title="" class="delete"><?php echo __('Delete') ?></a></span></div>
							</td>

							<td class="column-description desc">
								<div class="snapshot-description">
									<p><strong><?php echo __('Date') ?></strong>: <?php echo date($date_format, $snapshot['created']) ?></p>
									<p><strong><?php echo __('Size') ?></strong>: <?php echo $snapshot['size']; ?></p>
									<p><strong><?php echo __('Tables') ?></strong>: </p>
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
								<p><?php echo __('No snapshots found.') ?></p>
							</td>
						</tr>
				<?php endif ?>
			</tbody>
		</table>
	
	</form>
</div>