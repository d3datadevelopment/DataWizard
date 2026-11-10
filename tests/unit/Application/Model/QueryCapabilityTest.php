<?php

declare(strict_types=1);

namespace D3\DataWizard\tests\unit\Application\Model;

use D3\DataWizard\Application\Model\ActionBase;
use D3\DataWizard\Application\Model\ExportBase;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use ReflectionMethod;

/**
 * @covers \D3\DataWizard\Application\Model\ActionBase::hasQuery
 * @covers \D3\DataWizard\Application\Model\ExportBase::hasQuery
 */
class QueryCapabilityTest extends d3ModCfgUnitTestCase
{
    public function testDefaultQueryCapabilityIsPublicAndDoesNotBuildQuery(): void
    {
        foreach ([ActionBase::class => d3TestAction::class, ExportBase::class => d3TestExport::class] as $base => $class) {
            $this->assertTrue((new ReflectionMethod($base, 'hasQuery'))->isPublic());
            $task = $this->getMockBuilder($class)->onlyMethods(['getQuery'])->getMock();
            $task->expects($this->never())->method('getQuery');
            $this->assertTrue($task->hasQuery());
        }
    }
}
