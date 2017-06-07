<?php

namespace AssetManager\Service;

use Patchwork\JSqueeze;

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

    /** @var JSqueeze $jsSqueeze */
    private $jsSqueeze;

    public function __construct($assetFolder, $assetPath, $debug = false)
    {
        $assetFolder = rtrim($assetFolder, '/') . '/';
        $assetPath = rtrim($assetPath, '/') . '/';

        $this->jsFolder = $assetFolder;
        $this->jsPath = $assetPath;

        $this->jsCacheFolder = $assetFolder . 'dist/js/';
        $this->jsCachePath = $assetPath . 'dist/js/';

        $this->debug = $debug;
        $this->jsSqueeze = new JSqueeze();

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
        return $this->jsSqueeze->squeeze($jsData);
    }

    public function combineScriptsAndMangle(array $scripts)
    {
        $jsData = join(' ', array_map(function ($script) {
            return file_get_contents($script);
        }, $scripts));
        return $this->jsSqueeze->squeeze($jsData);
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
            return $this->jsCachePath . $versionedFile;
        }
        $combinedFilename = $this->createVersionedFile($combinedFilename);

        foreach ($paths as &$path) {
            if (file_exists($jsFolder . $path)) {
                $path = $jsFolder . $path;
            } elseif (!file_exists($path)) {
                unset($path);
            }
        }

        $result = $this->combineScriptsAndMangle($paths);
        file_put_contents($this->jsCacheFolder . $combinedFilename, $result);

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
