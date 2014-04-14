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

	<h3>Complete the following list to enable Snapshots:</h3>
	<dl class="wple-support-list">
		<dt <?php if(is_writable(wple_snapshot_dir())) echo 'class="wple-enabled"'; ?>>
			<span>Writable directory</span>
		</dt>
		<dd>Snapshots are saved in <code><?php echo str_replace(ABSPATH, '/', wple_snapshot_dir()); ?></code> and it must be writable</dd>

		<dt <?php if(wple_is_snapshot_dir_secure()) echo 'class="wple-enabled"'; ?>>
			<span>Secure directory</span>
		</dt>
		<dd>
			File permissions for <code><?php echo str_replace(ABSPATH, '/', wple_snapshot_dir()); ?></code> must be  <code>0740</code> or less.<br/>
			There must be an empty <code>index.html</code> file.
		</dd>
	</dl>
</div>