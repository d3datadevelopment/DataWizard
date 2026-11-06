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
use D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class RendererBridgeTest extends d3ModCfgUnitTestCase
{
    /** @var RendererBridge */
    protected $_oModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = new RendererBridge([new Csv(), new SpreadsheetCsv(), new Pretty(), new Json()]);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    public function testCustomRendererIsAvailableCaseInsensitively(): void
    {
        $renderer = $this->createMock(RendererInterface::class);
        $renderer->method('getFormat')->willReturn('custom');
        $renderer->method('getTitleTranslationId')->willReturn('CUSTOM_TITLE');
        $bridge = new RendererBridge([$renderer]);
        $this->assertSame($renderer, $bridge->getRenderer('CUSTOM'));
        $this->assertSame(['custom' => 'CUSTOM_TITLE'], $bridge->getTranslatedRendererIdList());
    }

    public function testDuplicateFormatsAreRejectedIgnoringCase(): void
    {
        $first = $this->createMock(RendererInterface::class);
        $first->method('getFormat')->willReturn('custom');
        $second = $this->createMock(RendererInterface::class);
        $second->method('getFormat')->willReturn('CUSTOM');
        $this->expectException(\InvalidArgumentException::class);
        new RendererBridge([$first, $second]);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getRendererList
     * @test
     * @throws ReflectionException
     */
    public function canGetRendererList()
    {
        $this->assertTrue((new \ReflectionMethod(RendererBridge::class, 'getRendererList'))->isPublic());
        $list = $this->_oModel->getRendererList();

        $this->assertIsArray($list);
        $this->assertArrayHasKey(Csv::FORMAT, $list);
        $this->assertInstanceOf(Csv::class, $list[Csv::FORMAT]);
        $this->assertArrayHasKey(SpreadsheetCsv::FORMAT, $list);
        $this->assertInstanceOf(SpreadsheetCsv::class, $list[SpreadsheetCsv::FORMAT]);
        $this->assertInstanceOf(SpreadsheetCsv::class, $this->_oModel->getRenderer('csv_spreadsheet'));
        $this->assertSame(
            'D3_DATAWIZARD_EXPORT_FORMAT_CSV_SPREADSHEET',
            $this->_oModel->getTranslatedRendererIdList()[SpreadsheetCsv::FORMAT]
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getTranslatedRendererIdList
     * @test
     * @throws ReflectionException
     */
    public function canGetTranslatedRendererIdList()
    {
        $utlist = $this->_oModel->getRendererList();

        $list = $this->_oModel->getTranslatedRendererIdList();

        $this->assertIsArray($list);
        $this->assertTrue((bool) count($list));
        $this->assertSame(count($utlist), count($list));
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::translateRendererId
     * @test
     * @throws ReflectionException
     */
    public function canTranslateRendererId()
    {
        $expected = "expectedTranslation";

        /** @var RendererInterface|MockObject $renderMock */
        $renderMock = $this->getMockBuilder(Pretty::class)
            ->onlyMethods(['getTitleTranslationId'])
            ->getMock();
        $renderMock->expects($this->atLeastOnce())->method('getTitleTranslationId')->willReturn($expected);

        $modelMock = $this->getMockBuilder(RendererBridge::class)
            ->onlyMethods(['getRendererList'])
            ->getMock();
        $modelMock->expects($this->once())
            ->method('getRendererList')
            ->willReturn(['Pretty' => $renderMock]);

        $translatedRendererIds = $modelMock->getTranslatedRendererIdList();

        $this->assertSame(['Pretty' => $expected], $translatedRendererIds);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\RendererBridge::getRenderer
     * @test
     * @param $format
     * @param $blThrowException
     * @throws ReflectionException
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
                'JSON' => $this->getMockBuilder(Json::class)->getMock(),
            ]
        );

        $this->_oModel = $modelMock;

        if ($blThrowException) {
            $this->expectException(NoSuitableRendererException::class);
        }

        $this->_oModel->getRenderer($format);
    }

    /**
     * @return array
     */
    public function canGetRendererDataProvider(): array
    {
        return [
            'existing renderer' => [Json::FORMAT, false],
            'unknown renderer' => ['unknownRenderer', true],
        ];
    }
}
