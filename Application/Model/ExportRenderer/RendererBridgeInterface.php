<?php

declare(strict_types=1);

namespace D3\DataWizard\Application\Model\ExportRenderer;

interface RendererBridgeInterface
{
    public function getRendererList(): array;
    public function getTranslatedRendererIdList(): array;
    public function getRenderer(string $format = Csv::FORMAT): RendererInterface;
}
