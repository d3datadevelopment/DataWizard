<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
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