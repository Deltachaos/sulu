<?php

/*
 * This file is part of Sulu.
 *
 * (c) Sulu GmbH
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Sulu\Bundle\AudienceTargetingBundle\Tests\Unit\Rule;

use DeviceDetector\DeviceDetector;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Sulu\Bundle\AudienceTargetingBundle\Rule\OperatingSystemRule;
use Symfony\Contracts\Translation\TranslatorInterface;

class OperatingSystemRuleTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @var DeviceDetector
     */
    private $deviceDetector;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var OperatingSystemRule
     */
    private $operatingSystemRule;

    public function setUp(): void
    {
        $this->deviceDetector = $this->prophesize(DeviceDetector::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);
        $this->operatingSystemRule = new OperatingSystemRule($this->deviceDetector->reveal(), $this->translator->reveal());
    }

    /**
     * @dataProvider provideEvaluate
     */
    public function testEvaluate($operatingSystemShortName, $options, $result)
    {
        $this->deviceDetector->getOs('short_name')->willReturn($operatingSystemShortName);
        $this->assertEquals($result, $this->operatingSystemRule->evaluate($options));
    }

    public function provideEvaluate()
    {
        return [
            ['LIN', ['os' => 'GNU/Linux'], true],
            ['GTV', ['os' => 'GNU/Linux'], false],
            [null, ['os' => 'GNU/Linux'], false],
            ['MAC', ['os' => 'Mac'], true],
            ['MAC', [], false],
        ];
    }
}
