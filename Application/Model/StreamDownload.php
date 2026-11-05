<?php

/**
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 */

declare(strict_types=1);

namespace D3\DataWizard\Application\Model;

use D3\ModCfg\Application\Model\download;

class StreamDownload extends download
{
    /** @param resource $stream */
    public function startStreamDownload(string $filename, $stream): void
    {
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($filename)) ?: 'download';
        $filename = trim($filename, '._-') ?: 'download';
        $this->sendHeader('Content-Type: ' . $this->getTypeByExtension($filename));
        $this->sendHeader('Content-Disposition: attachment; filename="' . $filename . '"');
        $this->sendHeader('Cache-Control: max-age=0');
        $this->allowLongDownload();
        fpassthru($stream);
        $this->finishDownload();
    }

    protected function sendHeader(string $value): void
    {
        header($value);
    }

    protected function allowLongDownload(): void
    {
        @set_time_limit(0);
    }

    /**
     * @codeCoverageIgnore The process exit is verified by a separate-process test.
     * @infection-ignore-all
     */
    protected function finishDownload(): void
    {
        exit;
    }
}
