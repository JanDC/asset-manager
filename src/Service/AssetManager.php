<?php

namespace AssetManager\Service;

use GK\JavascriptPacker;

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

    public function __construct($assetFolder, $assetPath, $debug = false)
    {
        $assetFolder = rtrim($assetFolder,'/').'/';
        $assetPath = rtrim($assetPath, '/') . '/';


        $this->jsFolder = $assetFolder . 'js/';
        $this->jsPath = $assetPath . 'js/';

        $this->jsCacheFolder = $assetFolder . 'dist/js/';
        $this->jsCachePath = $assetPath . 'dist/js/';

        $this->debug=$debug;

        if (!file_exists($this->jsCacheFolder)) {
            mkdir($this->jsCacheFolder, 0777, true);
        }
    }

    public function compileJsFromString($jsData)
    {
        $minifier = new JavascriptPacker($jsData);
        return $minifier->pack();
    }

    public function combineScriptsAndMangle(array $scripts)
    {
        return $this->compileJsFromString(implode(' ', $scripts));
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

        $scripts = array_map(function ($path) use ($jsFolder) {
            if (file_exists($jsFolder . $path)) {
                return file_get_contents($jsFolder . $path);
            } elseif ($attemptFetch = file_get_contents($path)) {
                return $attemptFetch;
            }
        }, $paths);

        if ($this->debug) {
            $result = implode(' ', $scripts);
        } else {
            $result = $this->combineScriptsAndMangle($scripts);
        }

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