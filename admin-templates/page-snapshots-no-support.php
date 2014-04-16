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