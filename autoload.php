<?php

$paths = [
    '' => ['/model/'],
    'System' => '/system/'
];

spl_autoload_register('initialize');

function initialize($className) {
    global $paths;

    foreach ($paths as $namespace => $dirs) {
        if (!is_array($dirs)) {
            include_class($namespace, $dirs, $className);
            continue;
        }

        foreach ($dirs as $dir) {
            include_class($namespace, $dir, $className);
        }
    }
}

function include_class($namespace, $dir, $class) {
    $rootPath = $_SERVER['DOCUMENT_ROOT'];
    $pos = strpos($class, '\\');
    $ns = $pos ? substr($class, 0, $pos) : "";
    $class = $pos ? substr($class, $pos + 1) : $class;

    if ($ns != $namespace) {
        return;
    }

    $dir = rtrim($dir, '/');
    $dir = "$rootPath/" .APP_NAME ."$dir";

    if (is_dir($dir)) {
        $file = "$dir/$class.php";
        $exists = file_exists($file);

        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "Class <strong>$class</strong> " .($exists ? "" : "does not") ." exist" .($exists ? "s" : "") ." in <strong>$dir</strong>/<br>";
        }
        
        if ($exists) {
            include $file;
        }
    }
}