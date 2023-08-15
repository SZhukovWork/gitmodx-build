<?php
/** @var xPDOTransport $transport */
/** @var array $options */
/** @var modX $modx */
if ($transport->xpdo) {
    $modx =& $transport->xpdo;

    switch ($options[xPDOTransport::PACKAGE_ACTION]) {
        case xPDOTransport::ACTION_INSTALL:
            $modx->addPackage('gitmodx', MODX_CORE_PATH . 'components/gitmodx/model/');
            $manager = $modx->getManager();
            $files = array(
                MODX_BASE_PATH.'index.php',
                MODX_CONNECTORS_PATH.'index.php',
                MODX_MANAGER_PATH.'index.php'
            );

            foreach($files as $file){
                $content = file_get_contents($file);
                $content = str_replace('model/modx/modx.class.php','components/gitmodx/model/gitmodx/gitmodx.class.php',$content);
                $content = str_replace('new modX(','new gitModx(',$content);
                file_put_contents($file,$content);
            }

            $coreIncFiles = array(
                MODX_BASE_PATH.'config.core.php' => "__DIR__.'/core/'",
                MODX_CONNECTORS_PATH.'config.core.php' => "dirname(__DIR__).'/core/'",
                MODX_MANAGER_PATH.'config.core.php' => "dirname(__DIR__).'/core/'",
            );

            foreach($coreIncFiles as $file => $replace){
                $content = file_get_contents($file);
                $content = preg_replace("#define\('MODX_CORE_PATH', '([^']+)'\);#mu", "define('MODX_CORE_PATH', {$replace});", $content);
                file_put_contents($file,$content);
            }
            /** @var modSystemSetting $systemSetting */
            $systemSetting = $modx->getObject('modSystemSetting', [
                'key' => 'parser_class'
            ]);
            $setPrimaryKeys = false;
            if(!$systemSetting){
                $systemSetting = $modx->newObject('modSystemSetting');
                $setPrimaryKeys = true;
            }
            $systemSetting->fromArray([
                'key' => 'parser_class',
                'value' => 'gitModParser',
                'xtype' => 'textfield',
                'namespace' => 'pdotools',
                'area' => 'pdotools_main',
                'editedon' => time()
            ],'',$setPrimaryKeys);
            $systemSetting->save();

            /** @var modSystemSetting $systemSetting */
            $systemSetting = $modx->getObject('modSystemSetting', [
                'key' => 'parser_class_path'
            ]);
            $setPrimaryKeys = false;
            if(!$systemSetting){
                $systemSetting = $modx->newObject('modSystemSetting');
                $setPrimaryKeys = true;
            }
            $systemSetting->fromArray([
                'key' => 'parser_class_path',
                'value' => '{core_path}components/gitmodx/model/gitmodx/',
                'xtype' => 'textfield',
                'namespace' => 'pdotools',
                'area' => 'pdotools_main',
                'editedon' => time()
            ],'',$setPrimaryKeys);
            $systemSetting->save();
            require_once(MODX_CORE_PATH . 'components/gitmodx/model/gitmodx/GitmodxService.php');
            $gitmodxService = new GitmodxService($modx);
            $gitmodxService->importTemplates();
            $gitmodxService->importChunks();
            $gitmodxService->importPlugins();
            $gitmodxService->importSnippets();
            break;
        case xPDOTransport::ACTION_UPGRADE:
            break;
        case xPDOTransport::ACTION_UNINSTALL:
            break;
    }
}

return true;