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

use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use League\Csv\Reader;
use OxidEsales\Eshop\Core\Config;

/**
 * @coversNothing
 */
class SpreadsheetCsvTest extends d3ModCfgUnitTestCase
{
    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::getContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeRows
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeValues
     */
    public function testEscapesFormulasInHeadersAndRowsWithoutBreakingCsv(): void
    {
        $renderer = $this->createRenderer(SpreadsheetCsv::class);
        $headers = (static function (): \Generator {
            yield '=header';
            yield 'plain';
        })();
        $rows = (static function (): \Generator {
            yield ['=1+1', 'safe'];
            yield ['+SUM(1;2)', '-1'];
            yield ['@command', "\ttext"];
            yield [" \rtext", "\ntext"];
            yield [" \u{00A0}=1", "\u{FEFF}+1"];
            yield ['＝1', ' ＠command'];
            yield ['normal;"quoted"', "safe\nnext line"];
            yield [42, null];
            yield [new class () implements \Stringable {
                public function __toString(): string
                {
                    return '=1+1';
                }
            }, -42];
        })();

        $reader = Reader::fromString($renderer->getContent($rows, $headers));
        $reader->setDelimiter(';');
        $this->assertSame([
            ["'=header", 'plain'],
            ["'=1+1", 'safe'],
            ["'+SUM(1;2)", "'-1"],
            ["'@command", "'\ttext"],
            ["' \rtext", "'\ntext"],
            ["' \u{00A0}=1", "'\u{FEFF}+1"],
            ["'＝1", "' ＠command"],
            ['normal;"quoted"', "safe\nnext line"],
            ['42', ''],
            ["'=1+1", '-42'],
        ], array_values(iterator_to_array($reader->getRecords())));
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::getTitleTranslationId
     */
    public function testSpreadsheetFormatHasTranslationId(): void
    {
        $renderer = new SpreadsheetCsv();

        $this->assertSame('D3_DATAWIZARD_EXPORT_FORMAT_CSV_SPREADSHEET', $renderer->getTitleTranslationId());
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::getContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeRows
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeValues
     */
    public function testRepeatedHeaderKeysAreHandledLikeRawCsv(): void
    {
        $headers = static function (): \Generator {
            yield 'first' => '=replaced';
            yield 'first' => '=kept';
            yield 'second' => 'plain';
        };

        $safeReader = Reader::fromString(
            $this->createRenderer(SpreadsheetCsv::class)->getContent([['one', 'two']], $headers())
        );
        $safeReader->setDelimiter(';');
        $rawReader = Reader::fromString(
            $this->createRenderer(Csv::class)->getContent([['one', 'two']], $headers())
        );
        $rawReader->setDelimiter(';');

        $this->assertSame(
            [["'=kept", 'plain'], ['one', 'two']],
            array_values(iterator_to_array($safeReader->getRecords()))
        );
        $this->assertSame(
            [['=kept', 'plain'], ['one', 'two']],
            array_values(iterator_to_array($rawReader->getRecords()))
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getContent
     */
    public function testRawCsvKeepsFormulaLikeValuesUnchanged(): void
    {
        $renderer = $this->createRenderer(Csv::class);
        $reader = Reader::fromString($renderer->getContent([['=1+1', '-1']], ['=header', 'plain']));
        $reader->setDelimiter(';');

        $this->assertSame([
            ['=header', 'plain'],
            ['=1+1', '-1'],
        ], array_values(iterator_to_array($reader->getRecords())));
    }

    private function createRenderer(string $class): Csv
    {
        $config = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $config->method('getConfigParam')->willReturnCallback(
            static fn (string $name) => $name === 'sCSVSign' ? ';' : '"'
        );
        $renderer = $this->getMockBuilder($class)->onlyMethods(['d3GetConfig'])->getMock();
        $renderer->method('d3GetConfig')->willReturn($config);

        return $renderer;
    }
}
