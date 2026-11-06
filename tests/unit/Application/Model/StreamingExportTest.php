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

namespace D3\DataWizard\tests\unit\Application\Model;

use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\Application\Model\Exceptions\RenderException;
use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\DataWizard\Application\Model\StreamDownload;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use D3\ModCfg\Application\Model\d3filesystem;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\Registry;

/**
 * @coversNothing
 */
class StreamingExportTest extends d3ModCfgUnitTestCase
{
    /**
     * @dataProvider streamingFormats
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeExport
     * @covers \D3\DataWizard\Application\Model\ExportBase::getStreamingRenderer
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::validateExportQuery
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::writeContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::configureCsv
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::writeContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeRows
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv::escapeValues
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::writeContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testFileExportReadsRowsIndividuallyAndPreservesExistingFile(string $format): void
    {
        $rows = [
            ['name' => '=formula', 'value' => 'a;b'],
            ['name' => 'second', 'value' => "line\nbreak"],
        ];
        $result = $this->createMock(Result::class);
        $result->expects($this->exactly(3))->method('fetchAssociative')
            ->willReturnOnConsecutiveCalls($rows[0], $rows[1], false);
        $result->expects($this->never())->method('fetchAllAssociative');
        $result->expects($this->once())->method('free');
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])->getMock();
        $connection->expects($this->once())->method('executeQuery')->with('SELECT 1', [])->willReturn($result);

        $directory = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($directory);
        unlink($directory);
        $this->assertTrue(mkdir($directory));
        $filename = 'Export.csv';
        $filteredFilename = oxNew(d3filesystem::class)->filterFilename($filename);
        $existing = $directory . DIRECTORY_SEPARATOR . $filteredFilename;
        file_put_contents($existing, 'existing');
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getQuery', 'getConnection', 'getExportFileName'])->getMock();
        $export->method('getQuery')->willReturn(['SELECT 1', []]);
        $export->method('getConnection')->willReturn($connection);
        $export->method('getExportFileName')->willReturn($filename);
        $created = null;

        try {
            $created = $this->callMethod($export, 'executeExport', [$format, dirname($existing)]);
            $expected = oxNew(RendererBridge::class)->getRenderer($format)->getContent($rows, array_keys($rows[0]));
            $this->assertNotSame($existing, $created);
            $this->assertSame('existing', file_get_contents($existing));
            $this->assertSame(dirname($existing), dirname($created));
            $base = pathinfo($filteredFilename, PATHINFO_FILENAME);
            $extension = pathinfo($filteredFilename, PATHINFO_EXTENSION);
            $this->assertMatchesRegularExpression(
                '/^' . preg_quote($base, '/') . '_[a-f0-9]{16}\.' . preg_quote($extension, '/') . '$/',
                basename($created)
            );
            $this->assertSame($expected, file_get_contents($created));
        } finally {
            if ($created !== null && is_file($created)) {
                unlink($created);
            }
            unlink($existing);
            rmdir($directory);
        }
    }

    public function streamingFormats(): array
    {
        return [
            'CSV' => [RendererBridge::FORMAT_CSV],
            'spreadsheet CSV' => [RendererBridge::FORMAT_CSV_SPREADSHEET],
            'JSON' => [RendererBridge::FORMAT_JSON],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     */
    public function testEmptyResultRaisesTaskExceptionAndReleasesResult(): void
    {
        $result = $this->createMock(Result::class);
        $result->expects($this->once())->method('fetchAssociative')->willReturn(false);
        $result->expects($this->once())->method('free');
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])->getMock();
        $connection->method('executeQuery')->willReturn($result);
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getQuery', 'getConnection'])->getMock();
        $export->method('getQuery')->willReturn(['SELECT 1', []]);
        $export->method('getConnection')->willReturn($connection);

        $originalLanguage = Registry::getLang();
        $language = $this->getMockBuilder(Language::class)->onlyMethods(['translateString'])->getMock();
        $language->expects($this->exactly(2))->method('translateString')->withConsecutive(
            ['D3_DATAWIZARD_ERR_NOEXPORTSELECT'],
            ['D3_DATAWIZARD_ERR_NOEXPORTCONTENT', null, true]
        )->willReturnOnConsecutiveCalls('Only SELECT is allowed', 'No export data');
        Registry::set(Language::class, $language);

        try {
            $this->expectException(TaskException::class);
            $this->expectExceptionMessage('No export data');
            $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_CSV, sys_get_temp_dir()]);
        } finally {
            Registry::set(Language::class, $originalLanguage);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeExport
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     */
    public function testBrowserExportReceivesStreamRatherThanAContentString(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls(['value' => 'one'], false);
        $result->expects($this->once())->method('free');
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])->getMock();
        $connection->method('executeQuery')->willReturn($result);
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getQuery', 'getConnection', 'getExportFileName', 'downloadStream'])->getMock();
        $export->method('getQuery')->willReturn(['SELECT 1', []]);
        $export->method('getConnection')->willReturn($connection);
        $export->method('getExportFileName')->willReturn('export.json');
        $downloaded = null;
        $export->expects($this->once())->method('downloadStream')->willReturnCallback(
            static function (string $filename, $stream) use (&$downloaded): void {
                $downloaded = [$filename, stream_get_contents($stream)];
            }
        );

        $this->assertSame('', $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_JSON, null]));
        $this->assertSame(['export.json', "[\n    {\n        \"value\": \"one\"\n    }\n]"], $downloaded);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::writeContent
     */
    public function testRenderingFailureReleasesDatabaseResult(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls(['value' => "\xB1\x31"], false);
        $result->expects($this->once())->method('free');
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])->getMock();
        $connection->method('executeQuery')->willReturn($result);
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getQuery', 'getConnection', 'downloadStream'])->getMock();
        $export->method('getQuery')->willReturn(['SELECT 1', []]);
        $export->method('getConnection')->willReturn($connection);
        $export->expects($this->never())->method('downloadStream');

        $this->expectException(RenderException::class);
        $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_JSON, null]);
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::validateExportQuery */
    public function testRejectsNonSelectQueryBeforeAccessingDatabase(): void
    {
        $export = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getConnection'])->getMock();
        $export->expects($this->never())->method('getConnection');

        $this->expectException(TaskException::class);
        $export->getExportData(['DELETE FROM test', []]);
    }

    /**
     * @dataProvider invalidQueryArguments
     * @covers \D3\DataWizard\Application\Model\ExportBase::validateExportQuery
     */
    public function testQueryArgumentsAreValidated($query, $parameters): void
    {
        $export = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getConnection'])->getMock();
        $export->expects($this->never())->method('getConnection');
        $this->expectException(\Assert\InvalidArgumentException::class);
        $export->getExportData([$query, $parameters]);
    }

    public function invalidQueryArguments(): array
    {
        return ['query must be string' => [123, []], 'parameters must be array' => ['SELECT 1', 'bad']];
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::validateExportQuery */
    public function testQueryWhitespaceIsTrimmedBeforeExecution(): void
    {
        $export = oxNew(d3TestExport::class);
        $this->assertSame(['SELECT 1', ['value']], $this->callMethod(
            $export,
            'validateExportQuery',
            [[" \tSELECT 1 \n", ['value']]]
        ));
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport */
    public function testTemporaryStreamIsClosedWhenQueryIsInvalid(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['createExportStream', 'getQuery'])->getMock();
        $export->method('createExportStream')->willReturn($stream);
        $export->method('getQuery')->willReturn(['DELETE FROM test', []]);

        try {
            $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_JSON, null]);
            $this->fail('Invalid query must raise an exception.');
        } catch (TaskException $exception) {
            $this->assertFalse(is_resource($stream));
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::getStreamingRenderer */
    public function testLegacyRendererAndOverriddenExportUseLegacyPath(): void
    {
        $export = oxNew(d3TestExport::class);
        $this->assertNull($this->callMethod($export, 'getStreamingRenderer', [RendererBridge::FORMAT_PRETTY]));

        $custom = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getContent'])->getMock();
        $this->assertNull($this->callMethod($custom, 'getStreamingRenderer', [RendererBridge::FORMAT_CSV]));

        $customRenderer = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getRenderer'])->getMock();
        $customRenderer->method('getRenderer')->willReturn(new class () extends Csv {});
        $this->assertNull($this->callMethod($customRenderer, 'getStreamingRenderer', [RendererBridge::FORMAT_CSV]));
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testRejectsNonlocalAndUnavailableDestinations(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        $existing = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($existing);
        $export = oxNew(d3TestExport::class);
        $this->assertTrue(stream_wrapper_register('d3remotetest', StreamingRemoteStream::class, STREAM_IS_URL));
        StreamingRemoteStream::$attempts = 0;

        try {
            foreach (['d3remotetest://export.csv', $existing . '/export.csv'] as $path) {
                try {
                    $this->callMethod($export, 'saveExportStream', [$path, $stream]);
                    $this->fail('An invalid destination must raise an export exception.');
                } catch (ExportFileException $exception) {
                    $this->assertFileExists($existing);
                }
            }
            $this->assertSame(0, StreamingRemoteStream::$attempts);
        } finally {
            stream_wrapper_unregister('d3remotetest');
            fclose($stream);
            unlink($existing);
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testCollisionRetainsExtensionAndAppliesFilePermissions(): void
    {
        $seed = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($seed);
        $path = $seed . '.csv';
        file_put_contents($path, 'existing');
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        fwrite($stream, 'new');
        rewind($stream);
        $fs = $this->getMockBuilder(d3filesystem::class)->onlyMethods(['chmod'])->getMock();
        $fs->expects($this->once())->method('chmod')->willReturn(true);
        $export = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getFileSystem'])->getMock();
        $export->method('getFileSystem')->willReturn($fs);
        $created = null;

        try {
            $created = $this->callMethod($export, 'saveExportStream', [$path, $stream]);
            $this->assertSame(dirname($path), dirname($created));
            $this->assertMatchesRegularExpression('/^' . preg_quote(basename($seed), '/') . '_[a-f0-9]{16}\.csv$/', basename($created));
            $this->assertSame('existing', file_get_contents($path));
            $this->assertSame('new', file_get_contents($created));
        } finally {
            fclose($stream);
            if ($created !== null && is_file($created)) {
                unlink($created);
            }
            unlink($path);
            unlink($seed);
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testIncompleteStreamCopyRemovesTheDestination(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        fwrite($stream, 'content');
        rewind($stream);
        fgetc($stream);
        $path = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($path);
        unlink($path);

        try {
            try {
                $this->callMethod(oxNew(d3TestExport::class), 'saveExportStream', [$path, $stream]);
                $this->fail('An incomplete copy must raise an export exception.');
            } catch (ExportFileException $exception) {
                $this->assertFileDoesNotExist($path);
            }
        } finally {
            fclose($stream);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testSubclassCanUseTheStreamFileExtensionPoint(): void
    {
        $export = new class () extends d3TestExport {
            public function saveFromSubclass(string $path, $stream): string
            {
                return parent::saveExportStream($path, $stream);
            }
        };
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        fwrite($stream, 'content');
        rewind($stream);
        $path = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($path);
        unlink($path);

        try {
            $this->assertSame($path, $export->saveFromSubclass($path, $stream));
            $this->assertSame('content', file_get_contents($path));
        } finally {
            fclose($stream);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    /**
     * @dataProvider failedCloseModes
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::publishExport
     */
    public function testFailedDestinationCloseRemovesPartialFile(bool $throws): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        fwrite($stream, 'content');
        rewind($stream);
        $path = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($path);
        unlink($path);
        $export = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['closeExportFile'])->getMock();
        $export->expects($this->once())->method('closeExportFile')->willReturnCallback(
            static function ($destination) use ($throws): bool {
                if ($throws) {
                    throw new \RuntimeException('close failed');
                }
                fclose($destination);
                return false;
            }
        );

        try {
            try {
                $this->callMethod($export, 'saveExportStream', [$path, $stream]);
                $this->fail('A close failure must raise an exception.');
            } catch (ExportFileException | \RuntimeException $exception) {
                $this->assertInstanceOf($throws ? \RuntimeException::class : ExportFileException::class, $exception);
                $this->assertFileDoesNotExist($path);
            }
        } finally {
            fclose($stream);
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    public function failedCloseModes(): array
    {
        return ['returns false' => [false], 'throws' => [true]];
    }

    /** @covers \D3\DataWizard\Application\Model\ExportBase::downloadStream */
    public function testDownloadStreamDelegatesToStreamDownload(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        $export = oxNew(d3TestExport::class);
        $download = $this->getMockBuilder(StreamDownload::class)
            ->onlyMethods(['startStreamDownload'])->getMock();
        $download->expects($this->once())->method('startStreamDownload')->with('export.csv', $stream);
        UtilsObject::setClassInstance(StreamDownload::class, $download);

        try {
            $this->callMethod($export, 'downloadStream', ['export.csv', $stream]);
        } finally {
            UtilsObject::resetClassInstances();
            fclose($stream);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     */
    public function testFailureToCreateTemporaryStreamStopsExport(): void
    {
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['createExportStream', 'getQuery'])->getMock();
        $export->expects($this->once())->method('createExportStream')->willReturn(false);
        $export->expects($this->never())->method('getQuery');

        $this->expectException(RenderException::class);
        $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_JSON, null]);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeStreamingExport
     */
    public function testFailureToRewindTemporaryStreamStopsExport(): void
    {
        $result = $this->createMock(Result::class);
        $result->method('fetchAssociative')->willReturnOnConsecutiveCalls(['value' => 'one'], false);
        $result->expects($this->once())->method('free');
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])->getMock();
        $connection->method('executeQuery')->willReturn($result);
        $export = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getQuery', 'getConnection', 'rewindExportStream', 'downloadStream'])->getMock();
        $export->method('getQuery')->willReturn(['SELECT 1', []]);
        $export->method('getConnection')->willReturn($connection);
        $export->expects($this->once())->method('rewindExportStream')->willReturn(false);
        $export->expects($this->never())->method('downloadStream');

        $this->expectException(RenderException::class);
        $this->callMethod($export, 'executeExport', [RendererBridge::FORMAT_JSON, null]);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::createExportStream
     * @covers \D3\DataWizard\Application\Model\ExportBase::rewindExportStream
     */
    public function testTemporaryExportStreamCanBeRewound(): void
    {
        $export = oxNew(d3TestExport::class);
        $stream = $this->callMethod($export, 'createExportStream');
        $this->assertIsResource($stream);

        try {
            fwrite($stream, 'content');
            $this->assertTrue($this->callMethod($export, 'rewindExportStream', [$stream]));
            $this->assertSame('content', stream_get_contents($stream));
        } finally {
            fclose($stream);
        }
    }
}

/** A local stream whose destination names always collide. */
class StreamingCollisionStream
{
    public $context;
    public static int $attempts = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$attempts++;
        return false;
    }

    public function url_stat(string $path, int $flags): array
    {
        return [2 => 0100644, 'mode' => 0100644];
    }
}

class StreamingRemoteStream
{
    public $context;
    public static int $attempts = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$attempts++;
        return false;
    }

    public function url_stat(string $path, int $flags): array|false
    {
        return false;
    }
}

class StreamingMissingStream
{
    public $context;
    public static int $attempts = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$attempts++;
        return false;
    }

    public function url_stat(string $path, int $flags): array|false
    {
        return false;
    }
}
