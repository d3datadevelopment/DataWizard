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

use MathieuViossat\Util\ArrayToTextTable;

class Pretty implements RendererInterface
{
    public function getContent($rows, $fieldNames) : string
    {
        $renderer = oxNew(ArrayToTextTable::class, $rows);
        return $renderer->getTable();
    }

    public function getFileExtension(): string
    {
        return 'txt';
    }
}