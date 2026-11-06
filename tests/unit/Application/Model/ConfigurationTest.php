<?php

declare(strict_types=1);

namespace D3\DataWizard\tests\unit\Application\Model;

use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;

class ConfigurationTest extends d3ModCfgUnitTestCase
{
    public function testTasksAreGroupedAndFoundById(): void
    {
        $action = new d3TestAction();
        $export = new d3TestExport();
        $configuration = new Configuration([$action], [$export]);
        $this->assertSame(['TEST_GROUP'], $configuration->getActionGroups());
        $this->assertSame(['TEST_GROUP'], $configuration->getExportGroups());
        $this->assertSame(['test_task' => $action], $configuration->getActionsByGroup('TEST_GROUP'));
        $this->assertSame(['test_task' => $export], $configuration->getExportsByGroup('TEST_GROUP'));
        $this->assertSame($action, $configuration->getActionById('test_task'));
        $this->assertSame($export, $configuration->getExportById('test_task'));
    }

    public function testDuplicateIdsAcrossGroupsAreRejected(): void
    {
        $second = $this->getMockBuilder(d3TestAction::class)->onlyMethods(['getGroup'])->getMock();
        $second->method('getGroup')->willReturn('OTHER_GROUP');
        $this->expectException(\InvalidArgumentException::class);
        new Configuration([new d3TestAction(), $second], []);
    }

    public function testInvalidIdIsRejected(): void
    {
        $action = $this->getMockBuilder(d3TestAction::class)->onlyMethods(['getTaskId'])->getMock();
        $action->method('getTaskId')->willReturn('invalid.id');
        $this->expectException(\InvalidArgumentException::class);
        new Configuration([$action], []);
    }

    public function testInvalidGroupIsRejected(): void
    {
        $action = $this->getMockBuilder(d3TestAction::class)->onlyMethods(['getGroup'])->getMock();
        $action->method('getGroup')->willReturn('invalid group');
        $this->expectException(\InvalidArgumentException::class);
        new Configuration([$action], []);
    }

    public function testWrongTaskTypeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Configuration([new d3TestExport()], []);
    }

    public function testUnknownActionRaisesException(): void
    {
        $this->expectException(DataWizardException::class);
        (new Configuration())->getActionById('missing');
    }

    public function testUnknownExportRaisesException(): void
    {
        $this->expectException(DataWizardException::class);
        (new Configuration())->getExportById('missing');
    }
}
