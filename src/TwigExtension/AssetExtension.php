<?php

namespace AssetManager\TwigExtension;

use AssetManager\Service\AssetManager;
use AssetManager\TwigExtension\AssetFunctions\CombineFunction;
use AssetManager\TwigExtension\AssetFunctions\JS_TokenParser;

class AssetExtension extends \Twig_Extension implements \Twig_Extension_InitRuntimeInterface
{
    /** @var AssetManager $assetManager */
    private $assetManager;

    /** @var  bool $debug */
    private $debug;

    public function __construct($assetFolder, $assetPath, $debug, $loadJS)
    {
        $this->debug = $debug;
        $this->assetManager = new AssetManager($assetFolder, $assetPath, $debug);
        $this->combineFunction = new CombineFunction($this->assetManager, $this->debug, $loadJS);
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
        return [
            new \Twig_SimpleFunction('combineJsAssets', [$this->combineFunction, 'combineAssets'],
                ['is_safe' => ['html', 'js'], 'needs_environment' => true])
        ];
    }

}