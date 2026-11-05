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

use Stringable;

class SpreadsheetCsv extends Csv
{
    public function getContent(iterable $rows, iterable $fieldNames): string
    {
        return parent::getContent($this->escapeRows($rows), $this->escapeValues($fieldNames));
    }

    public function getTitleTranslationId(): string
    {
        return 'D3_DATAWIZARD_EXPORT_FORMAT_CSV_SPREADSHEET';
    }

    private function escapeRows(iterable $rows): iterable
    {
        foreach ($rows as $row) {
            yield iterator_to_array($this->escapeValues($row));
        }
    }

    private function escapeValues(iterable $values): iterable
    {
        foreach ($values as $key => $value) {
            if ($value instanceof Stringable) {
                $value = (string) $value;
            }
            if (is_string($value) && preg_match(
                '/\A(?:[\x00-\x20\x7f]|\xC2\xA0|\xE1\x9A\x80|\xE2\x80[\x80-\x8A\xA8\xA9\xAF]|\xE2\x81\x9F|\xE3\x80\x80|\xEF\xBB\xBF)*(?:[=+\-@\x09\x0a\x0d]|\xEF\xBC[\x9D\x8B\x8D\xA0])/',
                $value
            ) !== 0) {
                $value = "'" . $value;
            }

            yield $key => $value;
        }
    }
}
