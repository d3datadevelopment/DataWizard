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

use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;

class RendererBridge
{
    const FORMAT_CSV    = 'CSV';
    const FORMAT_PRETTY = 'Pretty';

    /**
     * @param string $format
     *
     * @throws NoSuitableRendererException
     * @return RendererInterface
     */
    public function getRenderer($format = self::FORMAT_CSV): RendererInterface
    {
        switch ($format) {
            case self::FORMAT_CSV:
                return oxNew(Csv::class);
            case self::FORMAT_PRETTY:
                return oxNew(Pretty::class);
        }

        /** @var NoSuitableRendererException $e */
        $e = oxNew(NoSuitableRendererException::class, $format);
        throw $e;
    }
}