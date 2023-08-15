<?php

return [
    /* example
    'some_setting' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'gitmodx_main',
    ],*/

    'gitmodx_elements_exensions' => [
        'value' => json_encode([
            "modSnippet"=>"php",
            "modPlugin"=>"php",
            "modTemplate"=>"tpl",
            "modChunk"=>"tpl",
        ]),
        'area' => 'gitmodx_main',
    ],
    'gitmodx_base_folder' => [
        'value' => "components/gitmodx/elements/",
        'area' => 'gitmodx_main',
    ],
    'gitmodx_elements_folders' => [
        'value' => json_encode([
            "modChunk" => "chunks",
            "modTemplate" => "templates",
            "modSnippet" => "snippets",
            "modPlugin" => "plugins",
        ]),
        'area' => 'gitmodx_main',
    ],
];
