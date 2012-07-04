<?php

require_once('lessphp/lessc.inc.php');

function catdir($a, $b) {
    $a = preg_replace('/\/+$/', '', $a);
    $b = preg_replace('/^\/+/', '', $b);
    return $a . '/' . $b;
}

function comment() {
    $lines = func_get_args();
    echo "/**\n";
    foreach ( $lines as $line ) {
        echo " * $line\n";
    }
    echo "**/\n";
}

function lock_file($path) {
    if ( file_exists($path) ) {
        $perms = fileperms($path);
        $perms &= 0555;
        chmod($path, $perms);
    }
}

function main() {
    header('Content-Type: text/css');
    $document_root = $_SERVER['DOCUMENT_ROOT'];

    if ( isset($_GET['css']) ) {
        $request_css = $_GET['css'];
    } else if ( isset($_SERVER['PATH_INFO']) ) {
        $request_css = $_SERVER['PATH_INFO'];
    }

    $lock_css = true;
    if ( isset($_GET['lock']) ) {
        $lock_css = preg_match('/^(no|off|false|0)$/i', $_GET['lock'])? false: true;
    }

    if ( isset($request_css) ) {
        header("x-autoless-request-css: $request_css");
    }

    $request_css_pi = pathinfo($request_css);
    if ( !isset($request_css)
      || substr($request_css, 0, 1) != '/'
      || $request_css_pi['extension'] != 'css'
      || $request_css_pi['basename'] == $request_css_pi['extension'] ) {

        // Invalid request
        $file = explode(DIRECTORY_SEPARATOR, __FILE__);
        $file = array_pop($file);
        $css = isset($request_css)? $request_css: '';
        return comment(
            'Usage:',
            "$file?css=/URL-FULL-PATH/TO/LESS-FILE-NAME.css",
            'or',
            "$file/URL-FULL-PATH/TO/LESS-FILE-NAME.css",
            "Requested: $css"
        );
    }

    // Normalize paths
    $dir_path = $request_css_pi['dirname'];
    $dir_full_path = catdir($document_root, $dir_path);

    $css_basename = $request_css_pi['basename'];
    $request_min = preg_match('/[\-\.]min\.css$/i', $css_basename);
    $filename = preg_replace('/([\-\.]min)?\.css$/i', '', $css_basename);

    $less_basename = $filename . '.less';
    $css_basename = $filename . '.css';
    $css_min_basename = $filename . '.min.css';

    $less_full_path = catdir($dir_full_path, $less_basename);
    $css_full_path = catdir($dir_full_path, $css_basename);
    $css_min_full_path = catdir($dir_full_path, $css_min_basename);

    // Check existence
    if ( !file_exists($less_full_path) ) {
        if ( file_exists($css_full_path) ) {
            readfile($css_full_path);
            return;
        }

        if ( file_exists($css_min_full_path) ) {
            readfile($css_full_path);
            return;
        }

        header('HTTP/1.1 404 Not Found');
        return;
    }


    // Parse LESS
    try {
        $less = new lessc($less_full_path);
        $source = $less->parse();
        @unlink($css_full_path);
        file_put_contents($css_full_path, $source);
        if ( $lock_css ) lock_file($css_full_path);

        // min
        $less->setFormatter('compressed');
        $source = $less->parse();
        @unlink($css_min_full_path);
        file_put_contents($css_min_full_path, $source);
        if ( $lock_css ) lock_file($css_min_full_path);

        // Release source
        $source = null;
    } catch(Exctption $ex) {
        return comment(
            'lessphp fatal error:',
            $ex->getMessage()
        );
    }

    // Output
    if ( $request_min ) {
        readfile($css_min_full_path);
    } else {
        readfile($css_full_path);
    }
}

main();
