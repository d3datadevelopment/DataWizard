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
use InvalidArgumentException;

class RendererBridge implements RendererBridgeInterface
{
    public const FORMAT_QUERY  = 'QUERY';

    private array $renderers = [];

    public function __construct(iterable $renderers = [])
    {
        foreach ($renderers as $renderer) {
            if (!$renderer instanceof RendererInterface) {
                throw new InvalidArgumentException('Renderer must implement RendererInterface');
            }
            $format = $renderer->getFormat();
            if ($format === '' || strtolower($format) === strtolower(self::FORMAT_QUERY) || isset($this->renderers[strtolower($format)])) {
                throw new InvalidArgumentException('Empty, reserved or duplicate renderer format: ' . $format);
            }
            $this->renderers[strtolower($format)] = $renderer;
        }
    }

    public function getRendererList(): array
    {
        $list = [];
        foreach ($this->renderers as $renderer) {
            $list[$renderer->getFormat()] = $renderer;
        }
        return $list;
    }

    public function getTranslatedRendererIdList(): array
    {
        $rendererList = $this->getRendererList();
        array_walk($rendererList, [$this, 'translateRendererId']);
        return $rendererList;
    }

    /**
     * @param RendererInterface $instance
     */
    protected function translateRendererId(RendererInterface &$instance): void
    {
        $instance = $instance->getTitleTranslationId();
    }

    /**
     * @param string $format
     *
     * @return RendererInterface
     * @throws NoSuitableRendererException
     */
    public function getRenderer(string $format = Csv::FORMAT): RendererInterface
    {
        $format = strtolower($format);

        $rendererList = array_change_key_case($this->getRendererList());
        $rendererListTypes = array_keys($rendererList);

        if (in_array($format, $rendererListTypes, true)) {
            return $rendererList[$format];
        }

        /** @var NoSuitableRendererException $e */
        $e = oxNew(NoSuitableRendererException::class, $format);
        throw $e;
    }
}
