<?php

class GitmodxService
{
    public ModX $modx;
    private string $elementsFolder;
    public array $modElementsExtension = [
        modSnippet::class=>"php",
        modPlugin::class=>"php",
        modTemplate::class=>"tpl",
        modChunk::class=>"tpl",
    ];
    public array $modElementsFolders = [
        modChunk::class => "chunks",
        modTemplate::class => "templates",
        modSnippet::class => "snippets",
        modPlugin::class => "plugins",
    ];
    public int $source = 1;

    public function __construct(ModX $modx)
    {
        $this->modx = $modx;
        $this->elementsFolder = $this->modx->getOption("gitmodx_base_folder",null,MODX_CORE_PATH . "/components/gitmodx/elements/");
        $this->modElementsExtension = $this->modx->getOption("gitmodx_elements_exensions",null,$this->modElementsExtension);
        $this->modElementsFolders = $this->modx->getOption("gitmodx_elements_folders",null,$this->modElementsFolders);
        $this->source = $this->modx->getOption("gitmodx_source",null,$this->source);
    }

    public function importChunks()
    {
        $this->modx->loadClass(modChunk::class);
        $this->importElements(modChunk::class, $this->getModElementFolder(modChunk::class));
    }

    public function importPlugins()
    {
        $this->modx->loadClass(modPlugin::class);
        $this->importElements(modPlugin::class, $this->getModElementFolder(modPlugin::class));
    }

    public function importTemplates()
    {
        $this->modx->loadClass(modTemplate::class);
        $this->importElements(modTemplate::class, $this->getModElementFolder(modTemplate::class));
    }

    public function importSnippets()
    {
        $this->modx->loadClass(modSnippet::class);
        $this->importElements(modSnippet::class,$this->getModElementFolder(modSnippet::class));
    }

    public function loadElements(string $class)
    {
        //TODO: подгрузка в бд для админки
    }

    public function importElements(string $class, string $folder)
    {
        $elements = $this->modx->getCollection($class);
        $elementsPath = $this->elementsFolder . $folder . "/";
        foreach ($elements as $element) {
            /** @var modScript|modTemplate $element */
            $path = $elementsPath . $this->getModElementFilePath($element);
            $path = $this->normalizePath($path);
            if (!$this->createDir(dirname($path))) {
                $this->modx->log(MODX_LOG_LEVEL_ERROR, "Не удалось создать папку: " . dirname($path));
                continue;
            }
            if (file_put_contents($path, $element->getContent()) === false) {
                $this->modx->log(MODX_LOG_LEVEL_ERROR, "Не удалось создать файл: " . $path);
                continue;
            }
            $staticPath = str_replace($this->normalizePath(MODX_BASE_PATH), "", $path);
            $element->set("static", true);
            $element->set("static_file", $staticPath);
            $element->set('source', $this->source);
            if (!$element->save()) {
                $this->modx->log(MODX_LOG_LEVEL_ERROR, "Не удалось сохранить модель: " . $class);
            }
        }
    }

    private function getModElementFilePath(modElement $modElement): string
    {
        if ($modElement instanceof modTemplate) {
            $path = $modElement->get("templatename");
        } else if ($modElement instanceof modScript || $modElement instanceof modChunk) {
            $path = $modElement->get("name");
        }
        if (empty($path)) {
            throw new Exception("Не могу получить название элемента " . get_class($modElement));
        }
        $path .= "." . $this->getModElementExtension($modElement);
        $category = $modElement->getOne("Category");
        if ($category) {
            $path = $this->getCategoryPath($category) . $path;
        }
        return $path;
    }

    /**
     * @param $modElement
     * @return string|modElement
     */
    public function getModElementExtension($modElement): ?string
    {
        foreach ($this->modElementsExtension as $class => $extension) {
            if (is_a($modElement,$class,true)) {
                return $extension;

            }
        }
        return null;
    }

    /**
     * @param $modElement
     * @return string|modElement
     */
    public function getModElementFolder($modElement): ?string
    {
        foreach ($this->modElementsFolders as $class => $path) {
            if (is_a($modElement,$class,true)) {
                return $path;
            }
        }
        return null;
    }

    private function getCategoryPath(modCategory $category)
    {
        $path = $category->get("category") . "/";
        /** @var ?modCategory $parent */
        $parent = $category->getOne("Parent");
        if ($parent) {
            $path .= $this->getCategoryPath($parent);
        }
        return $path;
    }

    private function createDir(string $path)
    {
        if (!is_dir($path)) {
            return mkdir($path, 0777, true);
        }
        return true;
    }

    private function normalizePath(string $path)
    {
        return preg_replace("/[\\\\\/]+/", "/", $path);
    }
}