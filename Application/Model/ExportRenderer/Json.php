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

class Json implements RendererInterface
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
        $flags = JSON_PRETTY_PRINT;
        $json  = json_encode( $rows, $flags );
        if ( $json === false ) {
            throw oxNew( RenderException::class, json_last_error_msg(), json_last_error());
        }
        return $json;
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