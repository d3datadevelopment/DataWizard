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
use League\Csv\EncloseField;
use League\Csv\Exception;
use League\Csv\Writer;
use OxidEsales\Eshop\Core\Registry;

class Csv implements RendererInterface
{
    /**
     * @param $rows
     * @param $fieldNames
     *
     * @return string
     * @throws RenderException
     */
    public function getContent($rows, $fieldNames): string
    {
        try {
            $csv = $this->getCsv();
            $csv->insertOne( $fieldNames );
            $csv->insertAll( $rows );
            return $csv->toString();
        } catch (Exception $e) {
            /** @var RenderException $newException */
            $newException = oxNew(RenderException::class, $e->getMessage(), $e->getCode(), $e );
            throw $newException;
        }
    }

    public function getFileExtension(): string
    {
        return 'csv';
    }

    /**
     * @return Writer
     * @throws Exception
     */
    protected function getCsv(): Writer
    {
        $csv = Writer::createFromString();

        EncloseField::addTo($csv, "\t\x1f");

        $sEncloser = Registry::getConfig()->getConfigParam('sGiCsvFieldEncloser');
        if (false == $sEncloser) {
            $sEncloser = '"';
        }
        $csv->setEnclosure($sEncloser);

        $sDelimiter = Registry::getConfig()->getConfigParam('sCSVSign');
        if (false == $sDelimiter) {
            $sDelimiter = ';';
        }
        $csv->setDelimiter($sDelimiter);

        return $csv;
    }

    /**
     * @return string
     */
    public function getTitleTranslationId(): string
    {
        return 'D3_DATAWIZARD_EXPORT_FORMAT_CSV';
    }
}