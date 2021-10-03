<?php

spl_autoload_register('initialize');

function initialize($className) {
    $paths = require 'paths.php';

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
    $pos = strrpos($class, '\\');
    $ns = $pos ? substr($class, 0, $pos) : '';
    $class = $pos ? substr($class, $pos + 1) : $class;

    if (trim($ns, '\\') != trim($namespace, '\\')) {
        return;
    }

    $dir =  __DIR__ . '/' .trim($dir, '/');

    if (is_dir($dir)) {
        $file = "$dir/$class.php";
        $exists = file_exists($file);

        /* if (defined('DEBUG_MODE') && DEBUG_MODE) {
            echo "Class <strong>$class</strong> " .($exists ? '' : "does not") ." exist" .($exists ? "s" : '') ." in <strong>$dir</strong>/<br>";
        } */
        
        if ($exists) {
            include $file;
        }
    }
}