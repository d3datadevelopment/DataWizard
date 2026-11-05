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
use D3\DataWizard\Application\Model\ExportRenderer\Json;
use D3\DataWizard\Application\Model\ExportRenderer\StreamWriter;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;

/**
 * @coversNothing
 */
class StreamingRendererTest extends d3ModCfgUnitTestCase
{
    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::writeContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write
     */
    public function testEmptyJsonWritesEmptyArray(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        try {
            oxNew(Json::class)->writeContent($stream, [], []);
            rewind($stream);
            $this->assertSame('[]', stream_get_contents($stream));
        } finally {
            fclose($stream);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::writeContent
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::configureCsv
     */
    public function testCsvStreamReportsInconsistentColumns(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        try {
            $this->expectException(RenderException::class);
            oxNew(Csv::class)->writeContent($stream, [['one']], ['first', 'second']);
        } finally {
            fclose($stream);
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write */
    public function testStreamWriterReportsWriteFailure(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'd3stream_');
        $this->assertNotFalse($path);
        $stream = fopen($path, 'rb');
        $this->assertNotFalse($stream);
        try {
            $this->expectException(RenderException::class);
            StreamWriter::write($stream, 'content');
        } finally {
            fclose($stream);
            unlink($path);
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write */
    public function testStreamWriterWritesCompleteContent(): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        try {
            StreamWriter::write($stream, 'content');
            rewind($stream);
            $this->assertSame('content', stream_get_contents($stream));
        } finally {
            fclose($stream);
        }
    }

    /** @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write */
    public function testStreamWriterHandlesShortWrites(): void
    {
        $scheme = 'd3shortwritetest';
        $this->assertTrue(stream_wrapper_register($scheme, ShortWriteStream::class));
        ShortWriteStream::$content = '';
        $stream = fopen($scheme . '://output', 'wb');
        $this->assertNotFalse($stream);

        try {
            StreamWriter::write($stream, 'content');
            $this->assertSame('content', ShortWriteStream::$content);
        } finally {
            fclose($stream);
            stream_wrapper_unregister($scheme);
        }
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\StreamWriter::write
     */
    public function testStreamWriterRejectsLackOfProgress(): void
    {
        require_once dirname(__DIR__, 4) . '/tools/StreamWriterFunctionHooks.php';
        $GLOBALS['dataWizardTestNoProgressOnce'] = true;
        $stream = tmpfile();
        $this->assertNotFalse($stream);

        try {
            $this->expectException(RenderException::class);
            $this->expectExceptionMessage('Could not make progress writing export stream');
            StreamWriter::write($stream, 'content');
        } finally {
            fclose($stream);
        }
    }
}

class ShortWriteStream
{
    public $context;
    public static string $content = '';

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return true;
    }

    public function stream_write(string $data): int
    {
        $written = substr($data, 0, 2);
        self::$content .= $written;
        return strlen($written);
    }

    public function stream_flush(): bool
    {
        return true;
    }
}
