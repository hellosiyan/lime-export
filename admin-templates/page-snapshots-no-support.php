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
		<a href="<?php echo admin_url('tools.php?page=lime-export'); ?>" class="nav-tab"><?php _e('Database Export', 'lime-export'); ?></a><a href="<?php echo admin_url('tools.php?page=lime-snapshots'); ?>" class="nav-tab nav-tab-active"><?php _e('View Snapshots', 'lime-export'); ?></a>
	</h2>

	<h3><?php _e('Complete the following list to enable Snapshots', 'lime-export'); ?>:</h3>
	<dl class="wple-support-list">
		<dt <?php if(is_writable(wple_snapshot_dir())) echo 'class="wple-enabled"'; ?>>
			<span><?php _e('Writable directory', 'lime-export'); ?></span>
		</dt>
		<dd><?php printf( __( 'Snapshots are saved in <code>%s</code> and it must be writable', 'lime-export' ), str_replace(ABSPATH, '/', wple_snapshot_dir()) ); ?></dd>

		<dt <?php if(wple_is_snapshot_dir_secure()) echo 'class="wple-enabled"'; ?>>
			<span><?php _e('Secure directory', 'lime-export'); ?></span>
		</dt>
		<dd>
			<?php printf( __( 'File permissions for <code>%s</code> must be  <code>0740</code> or less.', 'lime-export' ), str_replace(ABSPATH, '/', wple_snapshot_dir()) ); ?><br/>
			<?php _e('There must be an empty <code>index.html</code> file.', 'lime-export'); ?>
		</dd>
	</dl>
</div>