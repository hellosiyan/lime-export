<?php 

define('WPLE_VERSION', '0.4');

define('WPLE_PATH', dirname(__FILE__));
define('WPLE_URL', WP_PLUGIN_URL . '/' . basename(WPLE_PATH) );

define('WPLE_MAX_QUERY_SIZE', 50000);

define('WPLE_MSG_NO_SELECTION', 1);
define('WPLE_MSG_NO_SPACE', 2);
define('WPLE_MSG_FILE_CREAT_ERROR', 3);
define('WPLE_MSG_FILE_READ_ERROR', 4);
define('WPLE_MSG_NOT_APACHE', 5);
define('WPLE_MSG_SNAPSHOT_NOT_FOUND', 6);

