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
use League\Csv\ColumnConsistency;
use League\Csv\Exception;
use League\Csv\Writer;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;

class Csv implements RendererInterface
{
    public function __construct(protected bool $forceEnclose = false)
    {}

    /**
     * @param iterable $rows
     * @param iterable $fieldNames
     *
     * @return string
     * @throws RenderException
     */
    public function getContent(iterable $rows, iterable $fieldNames): string
    {
        try {
            $csv = $this->getCsv();
            $this->forceEnclose ? $csv->forceEnclosure() : $csv->relaxEnclosure();
            $csv->insertOne((array) $fieldNames);
            $csv->insertAll($rows);
            return (string) $csv;
        } catch (Exception $e) {
            /** @var RenderException $newException */
            $newException = oxNew(RenderException::class, $e->getMessage(), $e->getCode(), $e);
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

        $sEncloser = $this->d3GetConfig()->getConfigParam('sGiCsvFieldEncloser') ?? '"';
        $csv->setEnclosure($sEncloser);

        $sDelimiter = $this->d3GetConfig()->getConfigParam('sCSVSign') ?? ';';
        $csv->setDelimiter($sDelimiter);

        $csv->addValidator(new ColumnConsistency(), 'columns_consistency');

        return $csv;
    }

    /**
     * @return string
     */
    public function getTitleTranslationId(): string
    {
        return 'D3_DATAWIZARD_EXPORT_FORMAT_CSV';
    }

    /**
     * @return Config
     */
    public function d3GetConfig(): Config
    {
        return Registry::getConfig();
    }
}
