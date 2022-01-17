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

interface RendererInterface
{
    /**
     * @param $rows
     * @param $fieldNames
     *
     * @return string
     */
    public function getContent($rows, $fieldNames): string;

    /**
     * @return string
     */
    public function getFileExtension(): string;

    /**
     * @return string
     */
    public function getTitleTranslationId(): string;
}
