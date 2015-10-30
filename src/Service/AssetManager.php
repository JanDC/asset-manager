<?php

namespace AssetManager\Service;

use GK\JavascriptPacker;
use UglifyJsWrapper\Options as JsWrapperOptions;
use UglifyJsWrapper\Wrapper as JsWrapper;

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
        $assetFolder = rtrim($assetFolder, '/') . '/';
        $assetPath = rtrim($assetPath, '/') . '/';

        $this->jsFolder = $assetFolder . 'js/';
        $this->jsPath = $assetPath . 'js/';

        $this->jsCacheFolder = $assetFolder . 'dist/js/';
        $this->jsCachePath = $assetPath . 'dist/js/';

        $this->debug = $debug;

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
        $tmpFile = '/tmp/jstmp-' . time() . '.js';
        file_put_contents($tmpFile, $jsData);
        return JsWrapper::execute($tmpFile, [JsWrapperOptions::MANGLE, JsWrapperOptions::COMPRESS]);
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

        $result = '';
        foreach ($paths as $path) {
            if (file_exists($jsFolder . $path)) {
                $result .= JsWrapper::execute($jsFolder . $path,
                    [JsWrapperOptions::MANGLE, JsWrapperOptions::COMPRESS]);
            } elseif ($attemptFetch = file_get_contents($path)) {
                $result .= JsWrapper::execute($path, [JsWrapperOptions::MANGLE, JsWrapperOptions::COMPRESS]);
            }
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