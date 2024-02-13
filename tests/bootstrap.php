<?php

! defined( 'ABSPATH') && define( 'ABSPATH', 'unit test' );

include_once dirname( __FILE__ ) .  '/../vendor/autoload.php';
include_once dirname( __FILE__ ) .  '/../omnisend/class-omnisend-core-bootstrap.php';

function register_uninstall_hook() {}
function add_action() {}
function apply_filters() { return array(); }
function get_option() {}
function plugin_dir_path() { return dirname( __FILE__ ) .  '/../omnisend/'; }

Omnisend_Core_Bootstrap::load();