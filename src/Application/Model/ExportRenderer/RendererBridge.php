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

namespace D3\DataWizard\Application\Model\ExportRenderer;

class RendererBridge
{
    const FORMAT_CSV    = 'CSV';
    const FORMAT_PRETTY = 'Pretty';

    /**
     * @param string $format
     *
     * @return RendererInterface
     */
    public function getRenderer($format = self::FORMAT_CSV): RendererInterface
    {
        switch ($format) {
            case self::FORMAT_PRETTY:
                return oxNew(Pretty::class);
            case self::FORMAT_CSV:
            default:
                return oxNew(Csv::class);
        }
    }
}