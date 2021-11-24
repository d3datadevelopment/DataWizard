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

namespace D3\DataWizard\tests\unit\Application\Controller\Admin;

use D3\DataWizard\Application\Controller\Admin\d3ActionWizard;
use D3\DataWizard\Application\Controller\Admin\d3ExportWizard;
use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3ExportWizardTest extends d3AdminControllerTest
{
    /** @var d3ExportWizard */
    protected $_oController;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oController = oxNew(d3ExportWizard::class);
    }

    /**
     * @covers d3ActionWizard::getGroups()
     * @test
     * @throws ReflectionException
     */
    public function canGetGroups()
    {
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getExportGroups'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportGroups')->willReturn($expected);

        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'getGroups'
            )
        );
    }

    /**
     * @covers d3ActionWizard::getGroupTasks()
     * @test
     * @throws ReflectionException
     * @dataProvider canGetGroupTasksDataProvider
     */
    public function canGetGroupTasks($argument)
    {
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getExportsByGroup'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportsByGroup')->with($argument)->willReturn($expected);

        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oController,
                'getGroupTasks',
                [$argument]
            )
        );
    }

    /**
     * @return array
     */
    public function canGetGroupTasksDataProvider(): array
    {
        return [
            ['test1'],
            ['test2']
        ];
    }

    /**
     * @covers d3ActionWizard::execute()
     * @test
     * @throws ReflectionException
     * @dataProvider executePassDataProvider
     */
    public function executePass($blDebug)
    {
        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(get_class(Registry::getRequest()))
            ->onlyMethods(['getRequestEscapedParameter'])
            ->getMock();

        $requestMock->expects($this->exactly($blDebug ? 1 : 2))->method('getRequestEscapedParameter')->withConsecutive(
            ['taskid'], ['format']
        )->willReturnOnConsecutiveCalls('testTaskId', 'CSV');
        Registry::set(Request::class, $requestMock);

        /** @var d3TestAction|MockObject $exportMock */
        $exportMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getQuery',
                'run'
            ])
            ->getMock();
        $exportMock->expects($this->atLeastOnce())->method('getQuery')->willReturn(['SELECT 1', ['1']]);
        $exportMock->expects($this->exactly((int) !$blDebug))->method('run')->willReturn('');

        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getExportById'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportById')->with('testTaskId')->willReturn($exportMock);
        $this->setValue($this->_oController, 'configuration', $configurationMock);

        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getConfigParam')->willReturn($blDebug);
        Registry::set(Config::class, $configMock);

        if ($blDebug) {
            $this->expectException(DebugException::class);
        }

        $this->callMethod(
            $this->_oController,
            'execute'
        );
    }

    /**
     * @return array
     */
    public function executePassDataProvider(): array
    {
        return [
            'no debug'  => [false],
            'debug'  => [true],
        ];
    }
}