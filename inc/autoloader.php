<?php
/*
 * REGISTRANDO AUTOLOADER DAS CLASSES PARA O PROJETO
 */
spl_autoload_register(function($class){
    // project-specific namespace prefix
    $prefix = 'lh';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . "/";

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $class) . '.php';
    
    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});