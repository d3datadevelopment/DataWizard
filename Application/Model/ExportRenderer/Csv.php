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

class Csv implements StreamingRendererInterface
{
    public const FORMAT = 'CSV';

    public function getFormat(): string
    {
        return self::FORMAT;
    }

    public function __construct(protected bool $forceEnclose = false)
    {
    }

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
            $this->forceEnclose ? $csv->forceEnclosure() : $csv->necessaryEnclosure();
            $csv->insertOne(
                is_array($fieldNames) ? $fieldNames : iterator_to_array($fieldNames)
            );
            $csv->insertAll($rows);
            return $csv->toString();
        } catch (Exception $e) {
            /** @var RenderException $newException */
            $newException = oxNew(RenderException::class, $e->getMessage(), []);
            throw $newException;
        }
    }

    public function getFileExtension(): string
    {
        return 'csv';
    }

    public function writeContent($stream, iterable $rows, iterable $fieldNames): void
    {
        try {
            $csv = Writer::fromStream($stream);
            $this->configureCsv($csv);
            $this->forceEnclose ? $csv->forceEnclosure() : $csv->necessaryEnclosure();
            $csv->insertOne(is_array($fieldNames) ? $fieldNames : iterator_to_array($fieldNames));
            $csv->insertAll($rows);
        } catch (Exception $e) {
            throw oxNew(RenderException::class, $e->getMessage(), []);
        }
    }

    /**
     * @return Writer
     * @throws Exception
     */
    protected function getCsv(): Writer
    {
        $csv = Writer::fromString();

        $this->configureCsv($csv);

        return $csv;
    }

    protected function configureCsv(Writer $csv): void
    {
        $sEncloser = $this->d3GetConfig()->getConfigParam('sGiCsvFieldEncloser') ?? '"';
        $csv->setEnclosure($sEncloser);

        $sDelimiter = $this->d3GetConfig()->getConfigParam('sCSVSign') ?? ';';
        $csv->setDelimiter($sDelimiter);

        $csv->addValidator(new ColumnConsistency(), 'columns_consistency');
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
