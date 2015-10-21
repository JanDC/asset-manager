<?php

namespace AssetManager\TwigExtension;

use AssetManager\Service\AssetManager;
use AssetManager\TwigExtension\AssetFunctions\CombineFunction;
use AssetManager\TwigExtension\AssetFunctions\JS_TokenParser;

class AssetExtension extends \Twig_Extension
{
    /**
     * @var AssetManager
     */
    private $assetManager;

    public function __construct($assetFolder, $assetPath, $debug)
    {
        $this->debug = $debug;
        $this->assetManager = new AssetManager($assetFolder, $assetPath, $debug);
        $this->combineFunction = new CombineFunction($this->assetManager);
    }

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'AssetExtension';
    }

    public function getAssetManager()
    {
        return $this->assetManager;
    }

    public function getTokenParsers()
    {
        return [
            (new JS_TokenParser())->setAssetManager($this->assetManager)->setDebug($this->debug)->setAssetExtensionName($this->getName())
        ];
    }

    public function getFilters()
    {
        return [];
    }

    public function getFunctions()
    {
        return [new \Twig_SimpleFunction('combineJsAssets', [$this->combineFunction, 'combineAssets'])];
    }
}