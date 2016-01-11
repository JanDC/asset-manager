<?php

namespace AssetManager\TwigExtension\AssetFunctions;

use AssetManager\Service\AssetManager;

class CombineFunction
{
    function __construct(AssetManager $assetManager, $debug = false)
    {
        $this->debug = $debug;
        $this->assetManager = $assetManager;
    }

    public function combineAssets(
        \Twig_Environment $twig,
        $combinedresultName,
        array $assetPaths,
        array $attributes,
        $foreReload = false
    ) {

        $this->registerWidgetTemplates($twig);

        if ($this->debug) {
            $assetManager = $this->assetManager;
            $assetPaths = array_map(function ($assetPath) use ($assetManager) {
                if (strpos($assetPath, $assetManager->getJsPath()) == false) {
                    return $assetManager->getJsPath() . $assetPath;
                }
                return $assetPath;
            }, $assetPaths);

            return $twig->render('@assetwidgets/combineAssetsList.twig',
                ['attributes' => $attributes, 'jsPaths' => $assetPaths]);
        } else {
            $combinedresult = $this->assetManager->combineLibsFromPaths($assetPaths, $combinedresultName, $foreReload);
            return $twig->render('@assetwidgets/combineAssets.twig',
                ['attributes' => $attributes, 'jsPath' => $combinedresult]);
        }
    }

    private function registerWidgetTemplates(\Twig_Environment &$twigEnvironment)
    {
        $currentLoader = $twigEnvironment->getLoader();
        if ($currentLoader instanceof \Twig_Loader_Filesystem) {
            $currentLoader->addPath(__DIR__ . '/../widgets', 'assetwidgets');
        } elseif ($currentLoader instanceof \Twig_Loader_Chain) {
            $fsloader = new \Twig_Loader_Filesystem();
            $fsloader->addPath(__DIR__ . '/../widgets', 'assetwidgets');
            $currentLoader->addLoader($fsloader);
        }
        $twigEnvironment->setLoader($currentLoader);
    }

}
