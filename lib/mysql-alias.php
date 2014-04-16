<?php

global $wpdb;

if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
	define('LIME_MYSQL_ASSOC', MYSQL_ASSOC);
	define('LIME_MYSQL_NUM', MYSQL_NUM);
} else {
	define('LIME_MYSQL_ASSOC', MYSQLI_ASSOC);
	define('LIME_MYSQL_NUM', MYSQLI_NUM);
}

function lime_mysql_fetch_array() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_fetch_array', func_get_args());
	} else {
		return call_user_func_array('mysql_fetch_array', func_get_args());
	}
}

function lime_mysql_query() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		$args = array_filter(func_get_args());
		if ( count($args) < 2 ) {
			$args = array_unshift($args, $wpdb->dbh);
		} else {
			$args = array_reverse($args);
		}
		return call_user_func_array('mysqli_query', $args);
	} else {
		return call_user_func_array('mysql_query', func_get_args());
	}
}

function lime_mysql_num_rows() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_num_rows', func_get_args());
	} else {
		return call_user_func_array('mysql_num_rows', func_get_args());
	}
}

function lime_mysql_free_result() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_free_result', func_get_args());
	} else {
		return call_user_func_array('mysql_free_result', func_get_args());
	}
}

function lime_mysql_num_fields() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_num_fields', func_get_args());
	} else {
		return call_user_func_array('mysql_num_fields', func_get_args());
	}
}

function lime_mysql_fetch_field() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_fetch_field', func_get_args());
	} else {
		return call_user_func_array('mysql_fetch_field', func_get_args());
	}
}

function lime_mysql_field_flags() {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return call_user_func_array('mysqli_fetch_field_direct', func_get_args());
	} else {
		return call_user_func_array('mysql_field_flags', func_get_args());
	}
}

function lime_mysql_is_numeric($field_info) {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return $field_info->flags & MYSQLI_NUM_FLAG;
	} else {
		return $field_info->numeric;
	}
}

function lime_mysql_is_blob($field_info) {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return $field_info->flags & MYSQLI_BINARY_FLAG;
	} else {
		return $field_info->blob;
	}
}

function lime_mysql_is_binary($field_flags) {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		if ( defined('MYSQLI_BINARY_FLAG') ) {
			return $field_flags->flags & MYSQLI_BINARY_FLAG;
		} else {
			return $field_flags->flags & 128;
		}
	} else {
		return stristr($field_flags, 'BINARY');
	}
}

function lime_mysql_is_bit($field_info) {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return $field_info->type == MYSQLI_TYPE_BIT;
	} else {
		return $field_info->type == 'bit';
	}
}

function lime_mysql_is_timestamp($field_info) {
	global $wpdb;
	if ( isset($wpdb->use_mysqli) && $wpdb->use_mysqli ) {
		return $field_info->type == MYSQLI_TYPE_TIMESTAMP;
	} else {
		return $field_info->type == 'timestamp';
	}
}
