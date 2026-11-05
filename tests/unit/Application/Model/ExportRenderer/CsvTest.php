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
use Generator;
use League\Csv\Exception;
use League\Csv\Writer;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class CsvTest extends ExportRendererTest
{
    /** @var Csv */
    protected $_oModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(Csv::class);
    }

    /**
     * @test
     *
     * @param bool $force
     *
     * @return void
     * @throws ReflectionException
     * @covers       \D3\DataWizard\Application\Model\ExportRenderer\Csv::__construct
     * @dataProvider canForceEncloseDataProvider
     */
    public function canForceEnclose(bool $force): void
    {
        $noForce = oxNew(Csv::class, $force);
        $this->assertSame(
            $force,
            $this->getValue(
                $noForce,
                'forceEnclose'
            )
        );
    }

    public function canForceEncloseDataProvider(): Generator
    {
        yield 'noForce' => [true];
        yield 'force' => [false];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     * @throws ReflectionException
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
        $onlyMethods = ['toString', 'insertOne', 'insertAll'];
        $csvMockBuilder->onlyMethods($onlyMethods);
        $csvMock = $csvMockBuilder->getMock();

        $csvMock->method('insertOne')->willReturn(1);
        $csvMock->method('insertAll')->willReturn(1);

        if ($blThrowException) {
            $csvMock->expects($this->atLeastOnce())->method('toString')->willThrowException(oxNew(Exception::class));
            $this->expectException(RenderException::class);
        } else {
            $csvMock->expects($this->atLeastOnce())->method('toString')->willReturn($expected);
        }

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
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     */
    public function canRenderRowsAndHeadersWithRequiredEnclosure(): void
    {
        $renderer = oxNew(Csv::class);
        $content = $renderer->getContent(
            [['plain', 'value;with;delimiter']],
            ['first', 'second']
        );

        $this->assertStringContainsString('first;second', $content);
        $this->assertStringContainsString('plain;"value;with;delimiter"', $content);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     */
    public function canForceEnclosureForEveryValue(): void
    {
        $renderer = oxNew(Csv::class, true);
        $content = $renderer->getContent([['plain', 'value']], ['first', 'second']);

        $this->assertStringContainsString('"first";"second"', $content);
        $this->assertStringContainsString('"plain";"value"', $content);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     */
    public function canRenderIterableFieldNames(): void
    {
        $fieldNames = (static function (): Generator {
            yield 'first';
            yield 'second';
        })();

        $content = oxNew(Csv::class)->getContent([['one', 'two']], $fieldNames);

        $this->assertStringContainsString('first;second', $content);
    }

    /**
     * @return array
     */
    public function canGetContentDataProvider(): array
    {
        return [
            'exception' => [true],
            'no exception' => [false],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getCsv
     * @test
     * @throws ReflectionException
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
     * @throws ReflectionException
     */
    public function canGetCsvNoSettings()
    {
        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getConfigParam')->willReturnCallback(
            function ($argName) {
                return match ($argName) {
                    'sGiCsvFieldEncloser', 'sCSVSign' => null,
                    default => Registry::getConfig()->getConfigParam($argName),
                };
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
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getCsv
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::configureCsv
     * @test
     */
    public function canUseConfiguredEnclosureAndDelimiter(): void
    {
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->method('getConfigParam')->willReturnCallback(
            static fn (string $name): ?string => match ($name) {
                'sGiCsvFieldEncloser' => "'",
                'sCSVSign' => '|',
                default => null,
            }
        );

        $renderer = $this->getMockBuilder(Csv::class)
            ->onlyMethods(['d3GetConfig'])
            ->getMock();
        $renderer->method('d3GetConfig')->willReturn($configMock);

        $csv = $this->callMethod($renderer, 'getCsv');

        $this->assertSame("'", $csv->getEnclosure());
        $this->assertSame('|', $csv->getDelimiter());
    }

    /** @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::configureCsv */
    public function testSubclassCanConfigureCsvWriter(): void
    {
        $renderer = new class () extends Csv {
            public function configureFromSubclass(Writer $writer): void
            {
                parent::configureCsv($writer);
            }
        };
        $writer = Writer::fromString();
        $renderer->configureFromSubclass($writer);

        $this->assertNotSame('', $writer->getDelimiter());
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getCsv
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     * @test
     */
    public function rejectsRowsWithInconsistentColumns(): void
    {
        $this->expectException(RenderException::class);

        oxNew(Csv::class)->getContent([['only-one-column']], ['first', 'second']);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::d3GetConfig
     * @test
     * @throws ReflectionException
     */
    public function canGetConfig()
    {
        $this->assertInstanceOf(
            Config::class,
            $this->_oModel->d3GetConfig()
        );
    }
}
