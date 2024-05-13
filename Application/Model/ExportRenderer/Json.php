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
use JsonException;

class Json implements RendererInterface
{
    /**
     * @param iterable $rows
     * @param iterable $fieldNames
     *
     * @return string
     * @throws RenderException
     */
    public function getContent( iterable $rows, iterable $fieldNames): string
    {
        try {
            $flags = JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
            return json_encode($rows, $flags);
        } catch (JsonException $e) {
            /** @var RenderException $newException */
            $newException = oxNew(RenderException::class, $e->getMessage(), $e->getCode(), $e);
            throw $newException;
        }
    }

    public function getFileExtension(): string
    {
        return 'json';
    }

    /**
     * @return string
     */
    public function getTitleTranslationId(): string
    {
        return "D3_DATAWIZARD_EXPORT_FORMAT_JSON";
    }
}
