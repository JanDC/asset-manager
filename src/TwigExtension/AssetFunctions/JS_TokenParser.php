<?php

namespace AssetManager\TwigExtension\AssetFunctions;

use AssetManager\Service\AssetManager;
use Twig_Token;
use Twig_TokenParser;

class JS_TokenParser extends Twig_TokenParser
{
    /** @var AssetManager $assetManager */
    private $assetManager;
    private $debug;

    public function parse(Twig_Token $token)
    {
        $lineno = $token->getLine();

        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
        $body = $this->parser->subparse([$this, 'decideJsEnd'], true);
        $this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

        return new JS_Node($body, $lineno, $this->getTag(), $this->assetManager, $this->assetExtensionName, $this->debug);
    }

    public function decideJsEnd(Twig_Token $token)
    {
        return $token->test('endmanglejs');
    }

    public function setAssetExtensionName($assetExtensionName)
    {
        $this->assetExtensionName = $assetExtensionName;
    }

    public function setAssetManager(AssetManager $assetManager)
    {
        $this->assetManager = $assetManager;
        return $this;
    }

    public function setDebug($debug)
    {
        $this->debug = $debug;
        return $this;
    }

    public function getTag()
    {
        return 'manglejs';
    }
}