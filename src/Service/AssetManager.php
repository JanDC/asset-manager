<?php

namespace AssetManager\Service;

use MatthiasMullie\Minify\JS;

class AssetManager
{
    /**
     * @var string
     */
    private $jsFolder;
    /**
     * @var string
     */
    private $jsCachePath;

    /**
     * @var bool
     */
    private $debug;

    /** @var  JS */
    private $jsMinify;

    public function __construct($assetFolder, $assetPath, $debug = false)
    {
        $assetFolder = rtrim($assetFolder, '/') . '/';
        $assetPath = rtrim($assetPath, '/') . '/';

        $this->jsFolder = $assetFolder;
        $this->jsPath = $assetPath;

        $this->jsCacheFolder = $assetFolder . 'dist/js/';
        $this->jsCachePath = $assetPath . 'dist/js/';

        $this->debug = $debug;
        $this->initializeJsMinify();

        if (!file_exists($this->jsCacheFolder)) {
            mkdir($this->jsCacheFolder, 0777, true);
        }
    }

    public function getJsFolder()
    {
        return $this->jsFolder;
    }

    public function getJsPath()
    {
        return $this->jsPath;
    }

    public function compileJsFromString($jsData)
    {
        return $this->jsMinify->add($jsData)->minify();
    }

    private function initializeJsMinify(){
        $this->jsMinify = new Js();
    }

    public function combineScriptsAndMangle(array $scripts, $path = null)
    {
        $this->initializeJsMinify();
        $this->jsMinify->add($scripts);

        return $this->jsMinify->minify($path);
    }

    public function combineLibsFromPaths(array $paths, $combinedFilename, $forceReload = false)
    {
        $jsFolder = $this->jsFolder;
        $versionedFile = $this->findVersionedFile(
            $combinedFilename,
            $this->jsCacheFolder,
            $forceReload || $this->debug
        );

        if ($versionedFile && !$forceReload && !$this->debug) {
            // File already present
            return $this->jsCachePath . $versionedFile;
        }
        $combinedFilename = $this->createVersionedFile($combinedFilename);

        // Validate paths
        foreach ($paths as &$path) {
            if (file_exists($jsFolder . $path)) {
                $path = $jsFolder . $path;
            } elseif (!file_exists($path)) {
                unset($path);
            }
        }

        $this->combineScriptsAndMangle($paths, $this->jsCacheFolder . $combinedFilename);
        return $this->jsCachePath . $combinedFilename;
    }

    /**
     * Detect a versioned targetfile
     *
     * @param $originalFile
     * @param $searchFolder
     * @param $clean
     *
     * @return bool|string
     */
    private function findVersionedFile($originalFile, $searchFolder, $clean = false)
    {
        $searchDir = opendir($searchFolder);
        while (($file = readdir($searchDir)) !== false) {
            if ($clean && !is_dir($searchFolder . $file)) {
                unlink($searchFolder . $file);
            }
            $tokens = explode('.', $file);
            $tokenLength = count($tokens);
            $extension = $tokens[$tokenLength - 1]; // $timestamp = $tokens[$tokenLength - 2];
            if (implode('.', array_slice($tokens, 0, $tokenLength - 2)) . ".{$extension}" == $originalFile) {
                closedir($searchDir);
                return $file;
            }
        }
        closedir($searchDir);
        return false;
    }

    private function createVersionedFile($originalFile)
    {
        $preTimestampTokens = explode('.', $originalFile, -1);
        $extension = explode('.', $originalFile)[count($preTimestampTokens)];
        $timestamp = time();
        $assembledString = implode('.', $preTimestampTokens) . ".$timestamp." . $extension;
        return $assembledString;
    }
}
