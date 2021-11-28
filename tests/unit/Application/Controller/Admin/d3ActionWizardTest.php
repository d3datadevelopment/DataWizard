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
use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\tests\tools\d3TestAction;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class d3ActionWizardTest extends d3AdminControllerTest
{
    /** @var d3ActionWizard */
    protected $_oController;

    protected $testClassName = d3ActionWizard::class;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oController = oxNew($this->testClassName);
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getGroups()
     * @test
     * @throws ReflectionException
     */
    public function canGetGroups()
    {
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getActionGroups'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getActionGroups')->willReturn($expected);

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
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getGroupTasks()
     * @test
     * @throws ReflectionException
     * @dataProvider canGetGroupTasksDataProvider
     */
    public function canGetGroupTasks($argument)
    {
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getActionsByGroup'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getActionsByGroup')->with($argument)->willReturn($expected);

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
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::execute()
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
        $requestMock->expects($this->any())->method('getRequestEscapedParameter')->willReturnCallback([$this, 'executePassRequestCallback']);
        Registry::set(Request::class, $requestMock);

        /** @var Config|MockObject $configMock */
        $configMock = $this->getMockBuilder(Config::class)
            ->onlyMethods(['getConfigParam'])
            ->getMock();
        $configMock->expects($this->atLeastOnce())->method('getConfigParam')->willReturnCallback(
            function ($argName) use ($blDebug) {
                switch ($argName) {
                    case 'd3datawizard_debug':
                        return $blDebug;
                    default:
                        return Registry::getConfig()->getConfigParam($argName);
                }
            }
        );

        /** @var d3ActionWizard|MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder(d3ActionWizard::class)
            ->onlyMethods(['d3GetConfig'])
            ->getMock();
        $controllerMock->method('d3GetConfig')->willReturn($configMock);
        $this->_oController = $controllerMock;

        /** @var d3TestAction|MockObject $actionMock */
        $actionMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods([
                'getQuery',
                'run'
            ])
            ->getMock();
        $actionMock->expects($this->atLeastOnce())->method('getQuery')->willReturn(['SELECT 1', ['1']]);
        $actionMock->expects($this->exactly((int) !$blDebug))->method('run')->willReturn(true);

        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getActionById'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getActionById')->with('testTaskId')->willReturn($actionMock);
        $this->setValue($this->_oController, 'configuration', $configurationMock);

        if ($blDebug) {
            $this->expectException(DebugException::class);
        }

        $this->callMethod(
            $this->_oController,
            'execute'
        );
    }

    public function executePassRequestCallback($varName)
    {
        switch ($varName) {
            case 'taskid':
                return 'testTaskId';
            case 'format':
                return RendererBridge::FORMAT_CSV;
            default:
                return oxNew(Request::class)->getRequestEscapedParameter($varName);
        }
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