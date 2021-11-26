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

use D3\DataWizard\Application\Model\Exceptions\RenderException;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use League\Csv\Exception;
use League\Csv\Writer;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;

class CsvTest extends ExportRendererTest
{
    /** @var Csv */
    protected $_oModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oModel = oxNew(Csv::class);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     * @throws \ReflectionException
     * @dataProvider canGetContentDataProvider
     */
    public function canGetContent($blThrowException)
    {
        $expected = 'expectedReturn';
        $fieldList = ['field1', 'field2'];
        $valueList = ['value1', 'value2'];

        /** @var Writer|MockObject $csvMock */
        $csvMockBuilder = $this->getMockBuilder(Writer::class);
        $csvMockBuilder->disableOriginalConstructor();
        $onlyMethods = ['insertOne', 'insertAll'];
        if (method_exists($csvMockBuilder->getMock(), 'getContent')) {
            $onlyMethods[] = 'getContent';
        } else {
            $csvMockBuilder->addMethods(['getContent']);
        }
        $csvMockBuilder->onlyMethods($onlyMethods);
        $csvMock = $csvMockBuilder->getMock();

        if ($blThrowException) {
            $csvMock->expects($this->atLeastOnce())->method('getContent')->willThrowException(oxNew(Exception::class));
            $this->expectException(RenderException::class);
        } else {
            $csvMock->expects($this->atLeastOnce())->method('getContent')->willReturn($expected);
        }
        $csvMock->expects($this->atLeastOnce())->method('insertOne')->with($fieldList)->willReturn(1);
        $csvMock->expects($this->atLeastOnce())->method('insertAll')->with($valueList)->willReturn(1);

        /** @var Csv|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Csv::class)
            ->onlyMethods(['getCsv'])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getCsv')->willReturn($csvMock);
        $this->_oModel = $modelMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oModel,
                'getContent',
                [$valueList, $fieldList]
            )
        );
    }

    /**
     * @return array
     */
    public function canGetContentDataProvider(): array
    {
        return [
            'exception' => [true],
            'no exception' => [false]
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getCsv
     * @test
     * @throws \ReflectionException
     */
    public function canGetCsv()
    {
        $this->assertInstanceOf(
            Writer::class,
            $this->callMethod(
                $this->_oModel,
                'getCsv'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getCsv
     * @test
     * @throws \ReflectionException
     */
    public function canGetCsvNoSettings()
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getConfigParam')->willReturnCallback(
            function ($argName) {
                switch ($argName) {
                    case 'sGiCsvFieldEncloser':
                    case 'sCSVSign':
                        return false;
                    default:
                        return Registry::getConfig()->getConfigParam($argName);
                }
            }
        );

        $modelMock = $this->getMockBuilder(Csv::class)
            ->onlyMethods(['d3GetConfig'])
            ->getMock();
        $modelMock->method('d3GetConfig')->willReturn($configMock);
        $this->_oModel = $modelMock;

        $csv = $this->callMethod(
            $this->_oModel,
            'getCsv'
        );

        $this->assertInstanceOf(
            Writer::class,
            $csv
        );

        $this->assertSame(
            '"',
            $csv->getEnclosure()
        );

        $this->assertSame(
            ';',
            $csv->getDelimiter()
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::d3GetConfig
     * @test
     * @throws \ReflectionException
     */
    public function canGetConfig()
    {
        $this->assertInstanceOf(
            Config::class,
            $this->callMethod(
                $this->_oModel,
                'd3GetConfig'
            )
        );
    }
}