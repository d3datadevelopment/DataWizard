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

use League\Csv\CannotInsertRecord;
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
     * @throws Exception
     * @throws CannotInsertRecord
     */
    public function getContent($rows, $fieldNames): string
    {
        $csv = $this->getCsv();
        $csv->insertOne($fieldNames);
        $csv->insertAll($rows);

        return $csv->getContent();
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
}