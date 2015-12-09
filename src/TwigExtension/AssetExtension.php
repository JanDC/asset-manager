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
    }

    public function initRuntime(\Twig_Environment $twigEnvironment)
    {
        $currentLoader = $twigEnvironment->getLoader();
        if ($currentLoader instanceof \Twig_Loader_Filesystem) {
            $currentLoader->addPath(__DIR__ . '/widgets', 'assetwidgets');
        } elseif ($currentLoader instanceof \Twig_Loader_Chain) {
            $fsloader = new \Twig_Loader_Filesystem();
            $fsloader->addPath(__DIR__ . '/widgets', 'assetwidgets');
            $currentLoader->addLoader($fsloader);
        }
        $twigEnvironment->setLoader($currentLoader);
        $this->combineFunction = new CombineFunction($this->assetManager, $twigEnvironment, $this->debug);
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
        return [new \Twig_SimpleFunction('combineJsAssets', [$this->combineFunction, 'combineAssets'], ['is_safe' => ['html', 'js']])];
    }

}