<?php

declare(strict_types=1);

namespace D3\DataWizard\tests\unit\Application;

use D3\DataWizard\Application\Controller\Admin\d3ActionWizard;
use D3\DataWizard\Application\Controller\Admin\d3ExportWizard;
use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\Pretty;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use ReflectionClass;

class PublicApiTest extends d3ModCfgUnitTestCase
{
    /**
     * @test
     * @dataProvider publicApiDataProvider
     */
    public function publicApiMethodsRemainPublic(string $className, array $methodNames): void
    {
        $reflection = new ReflectionClass($className);

        foreach ($methodNames as $methodName) {
            $this->assertTrue(
                $reflection->getMethod($methodName)->isPublic(),
                sprintf('%s::%s must remain public', $className, $methodName)
            );
        }
    }

    public function publicApiDataProvider(): array
    {
        return [
            'configuration' => [Configuration::class, [
                'configure',
                'registerAction',
                'registerExport',
                'getGroupedActions',
                'getGroupedExports',
                'getActionGroups',
                'getExportGroups',
                'getActionsByGroup',
                'getExportsByGroup',
                'getAllActions',
                'getAllExports',
                'getActionById',
                'getExportById',
            ]],
            'action wizard' => [d3ActionWizard::class, [
                'getViewId', 'getGroups', 'getGroupTasks', 'runTask', 'getSettingsService', 'getUserMessages', 'getHelpURL',
            ]],
            'export wizard' => [d3ExportWizard::class, [
                'getViewId', 'getGroups', 'getGroupTasks', 'runTask', 'getSettingsService', 'getUserMessages', 'getHelpURL',
            ]],
            'CSV renderer' => [Csv::class, ['getContent', 'getFileExtension', 'getTitleTranslationId', 'd3GetConfig']],
            'pretty renderer' => [Pretty::class, ['getContent', 'getFileExtension', 'getTitleTranslationId', 'getArrayToTextTableInstance']],
            'renderer bridge' => [RendererBridge::class, ['getRendererList', 'getTranslatedRendererIdList', 'getRenderer']],
        ];
    }
}
