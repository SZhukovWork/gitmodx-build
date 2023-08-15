<?php

if (!defined('MODX_CORE_PATH')) {
    $path = dirname(__FILE__);
    while (!file_exists($path . '/core/config/config.inc.php') && (strlen($path) > 1)) {
        $path = dirname($path);
    }
    define('MODX_CORE_PATH', $path . '/core/');
}

return [
    'name' => 'gitmodx',
    'name_lower' => 'gitmodx',
    'version' => '1.0.0',
    'release' => 'pl',
    // Install package to site right after build
    'install' => true,
    'set_debug_namespace'=> false,
    // Which elements should be updated on package upgrade
    'update' => [
        'plugins' => true,
        'settings' => false,
    ],
    // Which elements should be static by default
    'static' => [
        'plugins' => false,
        'snippets' => false,
        'chunks' => false,
    ],
    // Log settings
    'log_level' => 3,
    'log_target' => "ECHO",
    // Download transport.zip after build
    'download' => !empty($_REQUEST['download']) || true,
];
