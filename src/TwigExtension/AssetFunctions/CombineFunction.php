<?php

namespace AssetManager\TwigExtension\AssetFunctions;

use AssetManager\Service\AssetManager;

class CombineFunction
{
    function __construct(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
    }

    public function combineAssets($combinedresultName, array $assetPaths, $foreReload = false)
    {
        return $this->assetManager->combineLibsFromPaths($assetPaths, $combinedresultName, $foreReload);
    }
}
