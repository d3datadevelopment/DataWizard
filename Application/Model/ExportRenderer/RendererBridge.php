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

use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;

class RendererBridge
{
    const FORMAT_CSV    = 'CSV';
    const FORMAT_PRETTY = 'Pretty';
    const FORMAT_JSON   = 'JSON';

    /**
     * @return array
     */
    public function getRendererList(): array
    {
        return [
            self::FORMAT_CSV    => oxNew(Csv::class),
            self::FORMAT_PRETTY => oxNew(Pretty::class),
            self::FORMAT_JSON   => oxNew(Json::class)
        ];
    }

    public function getTranslatedRendererIdList()
    {
        $rendererList = $this->getRendererList();
        array_walk($rendererList, [$this, 'translateRendererId']);
        return $rendererList;
    }

    /**
     * @param RendererInterface $instance
     */
    protected function translateRendererId(RendererInterface &$instance)
    {
        $instance = $instance->getTitleTranslationId();
    }

    /**
     * @param string $format
     *
     * @return RendererInterface
     * @throws NoSuitableRendererException
     */
    public function getRenderer(string $format = self::FORMAT_CSV): RendererInterface
    {
        $format = strtolower($format);

        $rendererList = array_change_key_case($this->getRendererList(), CASE_LOWER);
        $rendererListTypes = array_keys(array_change_key_case($rendererList, CASE_LOWER));

        if (in_array($format, $rendererListTypes, true)) {
            return $rendererList[$format];
        }

        /** @var NoSuitableRendererException $e */
        $e = oxNew(NoSuitableRendererException::class, $format);
        throw $e;
    }
}