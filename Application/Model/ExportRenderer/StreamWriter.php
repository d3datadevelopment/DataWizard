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

namespace D3\DataWizard\Application\Model\ExportRenderer;

use D3\DataWizard\Application\Model\Exceptions\RenderException;

class StreamWriter
{
    /** @param resource $stream */
    public static function write($stream, string $content): void
    {
        $remaining = $content;
        while ($remaining !== '') {
            $written = @fwrite($stream, $remaining);
            if (!$written) {
                throw oxNew(RenderException::class, 'Could not write export stream', []);
            }
            $next = substr($remaining, $written);
            if (strlen($next) >= strlen($remaining)) {
                throw oxNew(RenderException::class, 'Could not make progress writing export stream', []);
            }
            $remaining = $next;
        }
    }
}
