<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * https://www.d3data.de
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author    D3 Data Development - Daniel Seifert <info@shopmodule.com>
 * @link      https://www.oxidmodule.com
 */

declare(strict_types=1);

namespace D3\DataWizard\tests\unit\Application\Model\ExportRenderer;

use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\Json;
use D3\DataWizard\Application\Model\ExportRenderer\Pretty;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class RendererBridgeTest extends d3ModCfgUnitTestCase
{
    /** @var RendererBridge */
    protected $_oModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oModel = oxNew(RendererBridge::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getRendererList
     * @test
     * @throws \ReflectionException
     */
    public function canGetRendererList()
    {
        $list = $this->callMethod(
            $this->_oModel,
            'getRendererList'
        );

        $this->assertIsArray($list);
        $this->assertTrue((bool) count($list));
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getTranslatedRendererIdList
     * @test
     * @throws \ReflectionException
     */
    public function canGetTranslatedRendererIdList()
    {
        $utlist = $this->callMethod(
            $this->_oModel,
            'getRendererList'
        );

        $list = $this->callMethod(
            $this->_oModel,
            'getTranslatedRendererIdList'
        );

        $this->assertIsArray($list);
        $this->assertTrue((bool) count($list));
        $this->assertSame(count($utlist), count($list));
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::translateRendererId
     * @test
     * @throws \ReflectionException
     */
    public function canTranslateRendererId()
    {
        $expected = "expectedTranslation";

        /** @var RendererInterface|MockObject $renderMock */
        $renderMock = $this->getMockBuilder(Pretty::class)
            ->onlyMethods(['getTitleTranslationId'])
            ->getMock();
        $renderMock->expects($this->atLeastOnce())->method('getTitleTranslationId')->willReturn($expected);

        $this->callMethod(
            $this->_oModel,
            'translateRendererId',
            [$renderMock]
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getRenderer
     * @test
     * @param $format
     * @param $blThrowException
     * @throws \ReflectionException
     * @dataProvider canGetRendererDataProvider
     */
    public function canGetRenderer($format, $blThrowException)
    {
        /** @var RendererBridge|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(RendererBridge::class)
            ->onlyMethods(['getRendererList'])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getRendererList')->willReturn(
            [
                'CSV' => $this->getMockBuilder(Csv::class)->getMock(),
                'Pretty' => $this->getMockBuilder(Pretty::class)->getMock(),
                'JSON' => $this->getMockBuilder(Json::class)->getMock()
            ]
        );

        $this->_oModel = $modelMock;

        if ($blThrowException) {
            $this->expectException(NoSuitableRendererException::class);
        }

        $this->callMethod(
            $this->_oModel,
            'getRenderer',
            [$format]
        );
    }

    /**
     * @return array
     */
    public function canGetRendererDataProvider(): array
    {
        return [
            'existing renderer'=> [RendererBridge::FORMAT_JSON, false],
            'unknown renderer'=> ['unknownRenderer', true]
        ];
    }
}