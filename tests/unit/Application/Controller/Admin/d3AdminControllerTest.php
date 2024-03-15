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
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use ReflectionException;

abstract class d3AdminControllerTest extends d3ModCfgUnitTestCase
{
    /** @var d3ActionWizard|d3ExportWizard */
    protected $_oController;

    protected $testClassName;

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oController);
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::__construct
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::__construct
     * @test
     * @throws ReflectionException
     */
    public function testConstructor()
    {
        $this->setValue($this->_oController, 'configuration', null);

        $this->callMethod(
            $this->_oController,
            '__construct'
        );

        $this->assertInstanceOf(
            Configuration::class,
            $this->getValue(
                $this->_oController,
                'configuration'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::runTask()
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::runTask()
     * @test
     * @throws ReflectionException
     */
    public function runTaskPass()
    {
        /** @var d3ActionWizard|d3ExportWizard|MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder($this->testClassName)
            ->onlyMethods(['execute'])
            ->getMock();
        $controllerMock->expects($this->once())->method('execute')->willReturn(true);

        $this->_oController = $controllerMock;

        $this->callMethod(
            $this->_oController,
            'runTask'
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::runTask()
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::runTask()
     * @test
     * @param $exceptionClass
     * @throws ReflectionException
     * @dataProvider runTaskFailedDataProvider
     */
    public function runTaskFailed($exceptionClass)
    {
        /** @var DataWizardException|DBALException|DatabaseErrorException|MockObject $exceptionMock */
        $exceptionMock = $this->getMockBuilder($exceptionClass)
            ->disableOriginalConstructor()
            ->getMock();
        $this->setValue($exceptionMock, 'message', 'exc_msg');

        /** @var d3ActionWizard|d3ExportWizard|MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder($this->testClassName)
            ->onlyMethods(['execute'])
            ->getMock();
        $controllerMock->expects($this->once())->method('execute')->willThrowException($exceptionMock);

        /** @var LoggerInterface|MockObject $loggerMock */
        $loggerMock = $this->getMockBuilder(get_class(Registry::getLogger()))
            ->onlyMethods(['error'])
            ->disableOriginalConstructor()
            ->getMock();
        $loggerMock->expects($this->atLeastOnce())->method('error')->with('exc_msg')->willReturn(true);
        Registry::set('logger', $loggerMock);

        /** @var UtilsView|MockObject $utilsViewMock */
        $utilsViewMock = $this->getMockBuilder(get_class(Registry::getUtilsView()))
            ->onlyMethods(['addErrorToDisplay'])
            ->getMock();
        $utilsViewMock->expects($this->atLeastOnce())->method('addErrorToDisplay')->with($exceptionMock)->willReturn(true);
        Registry::set(UtilsView::class, $utilsViewMock);

        $this->_oController = $controllerMock;

        $this->callMethod(
            $this->_oController,
            'runTask'
        );
    }

    /**
     * @return \string[][]
     */
    public function runTaskFailedDataProvider(): array
    {
        return [
            [DataWizardException::class],
            [DBALException::class],
            [DatabaseErrorException::class],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::execute()
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::execute()
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
        $requestMock->expects($this->atLeastOnce())->method('getRequestEscapedParameter')->with('taskid')->willReturn('testTaskId');
        Registry::set(Request::class, $requestMock);

        /** @var d3TestAction|MockObject $actionMock */
        $actionMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods([
                'getQuery',
                'run',
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

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getUserMessages()
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::getUserMessages()
     * @test
     * @throws ReflectionException
     */
    public function canGetUserMessages()
    {
        $this->assertNull(
            $this->callMethod(
                $this->_oController,
                'getUserMessages'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getHelpURL()
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::getHelpURL()
     * @test
     * @throws ReflectionException
     */
    public function canGetHelpUrl()
    {
        $this->assertNull(
            $this->callMethod(
                $this->_oController,
                'getHelpURL'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::d3GetConfig
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::d3GetConfig
     * @test
     * @throws ReflectionException
     */
    public function canGetConfig()
    {
        $this->assertInstanceOf(
            Config::class,
            $this->callMethod(
                $this->_oController,
                'd3GetConfig'
            )
        );
    }
}
