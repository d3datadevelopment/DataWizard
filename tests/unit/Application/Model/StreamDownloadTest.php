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

use D3\DataWizard\Application\Model\StreamDownload;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;

/**
 * @coversNothing
 */
class StreamDownloadTest extends d3ModCfgUnitTestCase
{
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers \D3\DataWizard\Application\Model\StreamDownload::startStreamDownload
     * @dataProvider filenames
     */
    public function testDownloadOutputsStreamWithSanitizedFilename(string $filename, string $expected): void
    {
        $stream = tmpfile();
        $this->assertNotFalse($stream);
        fwrite($stream, 'export content');
        rewind($stream);
        $download = $this->getMockBuilder(StreamDownload::class)
            ->onlyMethods(['getTypeByExtension', 'finishDownload', 'sendHeader', 'allowLongDownload'])->getMock();
        $download->expects($this->once())->method('getTypeByExtension')->with($expected)->willReturn('text/csv');
        $download->expects($this->exactly(3))->method('sendHeader')->withConsecutive(
            ['Content-Type: text/csv'],
            ['Content-Disposition: attachment; filename="' . $expected . '"'],
            ['Cache-Control: max-age=0']
        );
        $download->expects($this->once())->method('allowLongDownload');
        $download->expects($this->once())->method('finishDownload');

        ob_start();
        try {
            $download->startStreamDownload($filename, $stream);
            $this->assertSame('export content', ob_get_contents());
        } finally {
            ob_end_clean();
            fclose($stream);
        }
    }

    public function filenames(): array
    {
        return [
            'sanitized filename' => ['../a b?.csv', 'a_b_.csv'],
            'empty after trimming' => ['...', 'download'],
        ];
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers \D3\DataWizard\Application\Model\StreamDownload::sendHeader
     */
    public function testSendHeaderEmitsTheRequestedHeader(): void
    {
        require_once dirname(__DIR__, 3) . '/tools/StreamFunctionHooks.php';
        $GLOBALS['dataWizardTestHeaders'] = [];
        $download = new StreamDownload();
        $this->callMethod($download, 'sendHeader', ['X-DataWizard-Test: value']);
        $this->assertSame(['X-DataWizard-Test: value'], $GLOBALS['dataWizardTestHeaders']);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     * @covers \D3\DataWizard\Application\Model\StreamDownload::allowLongDownload
     */
    public function testDownloadTimeLimitIsDisabled(): void
    {
        if (!function_exists('set_time_limit')) {
            $this->markTestSkipped('set_time_limit is unavailable.');
        }

        set_time_limit(30);
        $this->callMethod(new StreamDownload(), 'allowLongDownload');
        $this->assertSame('0', ini_get('max_execution_time'));
    }

    /**
     * The process terminating after the download is observable only outside that process.
     * @coversNothing
     */
    public function testDownloadTerminatesProcessAfterStreaming(): void
    {
        if (!function_exists('proc_open')) {
            $this->markTestSkipped('Starting a local PHP subprocess is unavailable.');
        }

        $script = <<<'PHP'
            namespace D3\ModCfg\Application\Model {
                class download {
                    public function getTypeByExtension(string $filename): string { return 'text/csv'; }
                }
            }
            namespace {
                require $argv[1];
                $stream = fopen('php://temp', 'w+b');
                fwrite($stream, 'export content');
                rewind($stream);
                (new \D3\DataWizard\Application\Model\StreamDownload())->startStreamDownload('export.csv', $stream);
                echo 'unexpected continuation';
            }
            PHP;
        $source = dirname(__DIR__, 4) . '/Application/Model/StreamDownload.php';
        $pipes = [];
        $process = proc_open([PHP_BINARY, '-r', $script, $source], [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        $this->assertIsResource($process);
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $status = proc_close($process);

        $this->assertSame(0, $status, $errors);
        $this->assertSame('', $errors);
        $this->assertSame('export content', $output);
    }
}
