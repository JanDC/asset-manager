<?php

namespace AssetManager\TwigExtension\AssetFunctions;

use Twig_Compiler;
use Twig_Node;
use Twig_NodeInterface;
use AssetManager\Service\AssetManager;

class JS_Node extends Twig_Node
{
    /** @var AssetManager $assetManager */
    private $assetManager;

    /** @var bool */
    private $debug;

    public function __construct(Twig_NodeInterface $body, $lineno, $tag, AssetManager $assetManager,$assetExtensionName='AssetExtension', $debug = false)
    {
        $this->assetManager = $assetManager;
        $this->debug = $debug;
        $this->assetExtensionName = $assetExtensionName;
        parent::__construct(array('body' => $body), [], $lineno, $tag);
    }

    public function compile(Twig_Compiler $compiler)
    {
        //Debug is true => passthrough, otherwise we mangle
        if ($this->debug) {
            $compiler
                ->addDebugInfo($this)
                ->subcompile($this->getNode('body'));
        } else {
            $compiler
                ->addDebugInfo($this)
                ->write("ob_start();\n")
                ->subcompile($this->getNode('body'))
                ->write("echo \$this->env->getExtension('AssetExtension')->assetManager->compileJsFromString(")
                ->raw('trim(ob_get_clean())')
                ->raw(");\n");
        }
    }
}