<?php

/* START | Autoloader */
require_classes(__DIR__ . '/../../omnisend/includes');

function require_classes($directory) {
    if (!is_dir($directory)) {
        return;
    }

    $files = scandir($directory);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $file;

        if (is_dir($filePath)) {
            require_classes($filePath);
        } else if (strpos($file, 'class-') === 0 && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            spl_autoload_register(function($class) use ($filePath) {
                if (file_exists($filePath)) {
                    require_once $filePath;
                }
            });
        }
    }
}
/* END | Autoloader */


/* START | Fix WordPress requirements */
require_once(__DIR__ . '/list/class-wp-error.php');
require_once(__DIR__ . '/list/formatting.php');

function do_action($args) {
    return;
}

function apply_filters($test = false, $test2 = false, $test3 = false) {
    return $test2;
}

function is_utf8_charset( $blog_charset = null ) {
    return _is_utf8_charset( $blog_charset ?? 'UTF-8' );
}

function _is_utf8_charset( $charset_slug ) {
    if ( ! is_string( $charset_slug ) ) {
        return false;
    }

    return (
        0 === strcasecmp( 'UTF-8', $charset_slug ) ||
        0 === strcasecmp( 'UTF8', $charset_slug )
    );
}

function get_option($param = '') {
    return 'test';
}

define('ABSPATH', 'tests');

/* END | Fix WordPress requirements */