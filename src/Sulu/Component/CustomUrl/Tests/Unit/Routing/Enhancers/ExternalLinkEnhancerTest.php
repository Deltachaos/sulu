<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Component\CustomUrl\Tests\Unit\Routing\Enhancers;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Component\Content\Compat\Structure;
use Sulu\Component\Content\Compat\Structure\PageBridge;
use Sulu\Component\CustomUrl\Routing\Enhancers\ExternalLinkEnhancer;
use Symfony\Component\HttpFoundation\Request;

class ExternalLinkEnhancerTest extends TestCase
{
    use ProphecyTrait;

    public function testEnhance()
    {
        $structure = $this->prophesize(PageBridge::class);
        $structure->getNodeType()->willReturn(Structure::NODE_TYPE_EXTERNAL_LINK);
        $structure->getResourceLocator()->willReturn('/test');
        $request = $this->prophesize(Request::class);

        $enhancer = new ExternalLinkEnhancer();
        $defaults = $enhancer->enhance(['_structure' => $structure->reveal()], $request->reveal());

        $this->assertEquals(
            [
                '_structure' => $structure->reveal(),
                '_controller' => 'sulu_website.redirect_controller::redirectAction',
                'url' => '/test',
            ],
            $defaults
        );
    }

    public function testEnhanceNoStructure()
    {
        $structure = $this->prophesize(PageBridge::class);
        $structure->getNodeType()->willReturn(Structure::NODE_TYPE_EXTERNAL_LINK);
        $structure->getResourceLocator()->willReturn('/test');
        $request = $this->prophesize(Request::class);

        $enhancer = new ExternalLinkEnhancer();
        $defaults = $enhancer->enhance([], $request->reveal());

        $this->assertEquals([], $defaults);
    }

    public function testEnhanceInternalLink()
    {
        $structure = $this->prophesize(PageBridge::class);
        $structure->getNodeType()->willReturn(Structure::NODE_TYPE_INTERNAL_LINK);
        $structure->getResourceLocator()->willReturn('/test');
        $request = $this->prophesize(Request::class);

        $enhancer = new ExternalLinkEnhancer();
        $defaults = $enhancer->enhance(['_structure' => $structure->reveal()], $request->reveal());

        $this->assertEquals(['_structure' => $structure->reveal()], $defaults);
    }
}
