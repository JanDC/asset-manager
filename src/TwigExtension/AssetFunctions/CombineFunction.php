<?php

namespace AssetManager\TwigExtension\AssetFunctions;

use AssetManager\Service\AssetManager;

class CombineFunction
{
    function __construct(AssetManager $assetManager, \Twig_Environment $twig, $debug = false)
    {
        $this->debug = $debug;
        $this->twig = $twig;
        $this->assetManager = $assetManager;
    }

    public function combineAssets($combinedresultName, array $assetPaths, array $attributes, $foreReload = false)
    {
        if ($this->debug) {
            $assetManager = $this->assetManager;
            $assetPaths = array_map(function ($assetPath) use ($assetManager) {
                if (strpos($assetPath, $assetManager->getJsPath()) == FALSE) {
                    return $assetManager->getJsPath() . $assetPath;
                }
                return $assetPath;
            }, $assetPaths);

            return $this->twig->render('@assetwidgets/combineAssetsList.twig', ['attributes' => $attributes, 'jsPaths' => $assetPaths]);
        } else {
            $combinedresult = $this->assetManager->combineLibsFromPaths($assetPaths, $combinedresultName, $foreReload);
            return $this->twig->render('@assetwidgets/combineAssets.twig', ['attributes' => $attributes, 'jsPath' => $combinedresult]);
        }
    }
}
