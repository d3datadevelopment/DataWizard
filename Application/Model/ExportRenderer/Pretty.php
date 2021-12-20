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

use MathieuViossat\Util\ArrayToTextTable;

class Pretty implements RendererInterface
{
    /**
     * @param $rows
     * @param $fieldNames
     *
     * @return string
     */
    public function getContent($rows, $fieldNames) : string
    {
        $renderer = $this->getArrayToTextTableInstance($rows);
        return $renderer->getTable();
    }

    /**
     * @param $rows
     * @return ArrayToTextTable
     */
    public function getArrayToTextTableInstance($rows): ArrayToTextTable
    {
        return oxNew(ArrayToTextTable::class, $rows);
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return 'txt';
    }

    /**
     * @return string
     */
    public function getTitleTranslationId(): string
    {
        return "D3_DATAWIZARD_EXPORT_FORMAT_PRETTY";
    }
}