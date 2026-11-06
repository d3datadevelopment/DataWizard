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
use D3\DataWizard\Application\Model\Constants;
use D3\DataWizard\Application\Model\ActionResult;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\tests\tools\d3TestAction;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\Language;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use Psr\Log\LoggerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class d3ActionWizardTest extends d3AdminController
{
    /** @var d3ActionWizard */
    protected $_oController;

    protected $testClassName = d3ActionWizard::class;

    public function setUp(): void
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
        $this->assertControllerMethodPublic('getGroups');
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getActionGroups'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getActionGroups')->willReturn($expected);

        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->assertSame(
            $expected,
            $this->_oController->getGroups()
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
        $this->assertControllerMethodPublic('getGroupTasks');
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getActionsByGroup'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getActionsByGroup')->with($argument)->willReturn($expected);

        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->assertSame(
            $expected,
            $this->_oController->getGroupTasks($argument)
        );
    }

    /**
     * @return array
     */
    public function canGetGroupTasksDataProvider(): array
    {
        return [
            ['test1'],
            ['test2'],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::execute()
     * @test
     * @throws ReflectionException
     * @dataProvider executePassDataProvider
     */
    public function executePass($blDebug, $format = null)
    {
        /** @var Request|MockObject $requestMock */
        $requestMock = $this->getMockBuilder(get_class(Registry::getRequest()))
            ->onlyMethods(['getRequestEscapedParameter'])
            ->getMock();
        $requestMock->expects($this->any())->method('getRequestEscapedParameter')->willReturnCallback(
            static fn ($name) => $name === 'taskid' ? 'testTaskId' : $format
        );
        Registry::set(Request::class, $requestMock);

        /** @var ModuleSettingService $settingsServiceMock */
        $settingsServiceMock = $this->getMockBuilder(ModuleSettingService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBoolean'])
            ->getMock();
        $settingsServiceMock->expects($this->never())->method('getBoolean');

        /** @var d3ActionWizard|MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder(d3ActionWizard::class)
            ->onlyMethods(['getSettingsService'])
            ->getMock();
        $controllerMock->method('getSettingsService')->willReturn($settingsServiceMock);
        $this->_oController = $controllerMock;

        /** @var d3TestAction|MockObject $actionMock */
        $actionMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods([
                'getQuery',
                'run',
                'init',
            ])
            ->getMock();
        $actionMock->expects($this->exactly((int) $blDebug))->method('getQuery')->willReturn(['SELECT 1', ['1']]);
        $actionMock->expects($this->exactly((int) !$blDebug))->method('run');
        $actionMock->expects($this->once())->method('init');
        $this->setValue($actionMock, 'actionResult', new ActionResult(5));

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
        $this->assertNull($this->_oController->getActionSuccessMessage());
    }

    public function executePassRequestCallback($varName)
    {
        return match ($varName) {
            'taskid' => 'testTaskId',
            'format' => RendererBridge::FORMAT_CSV,
            default => oxNew(Request::class)->getRequestEscapedParameter($varName),
        };
    }

    /**
     * @return array
     */
    public function executePassDataProvider(): array
    {
        return [
            'missing format executes action' => [false, null],
            'empty format executes action' => [false, ''],
            'query preview' => [true, 'query'],
        ];
    }

    /**
     * @dataProvider successfulActionResults
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::execute
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::runTask
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getActionSuccessMessage
     */
    public function testSuccessfulActionIsDisplayedWithoutLoggingAnError(
        int $affected,
        string $translationId,
        string $expectedMessage
    ): void {
        $this->assertTrue((new \ReflectionMethod(d3ActionWizard::class, 'getActionSuccessMessage'))->isPublic());
        $oldRequest = Registry::getRequest();
        $oldLanguage = Registry::getLang();
        $oldLogger = Registry::getLogger();
        $oldUtilsView = Registry::getUtilsView();
        $request = $this->getMockBuilder(Request::class)->onlyMethods(['getRequestEscapedParameter'])->getMock();
        $request->method('getRequestEscapedParameter')->willReturnCallback(
            static fn ($name) => $name === 'taskid' ? 'task-id' : ''
        );
        $language = $this->getMockBuilder(Language::class)->onlyMethods(['translateString'])->getMock();
        $language->expects($this->once())->method('translateString')->with($translationId)->willReturn('%s affected');
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('error');
        $utilsView = $this->getMockBuilder(UtilsView::class)->onlyMethods(['addErrorToDisplay'])->getMock();
        $utilsView->expects($this->never())->method('addErrorToDisplay');
        $settings = $this->getMockBuilder(ModuleSettingService::class)->disableOriginalConstructor()
            ->onlyMethods(['getBoolean'])->getMock();
        $settings->method('getBoolean')->willReturn(false);
        $action = $this->getMockBuilder(d3TestAction::class)->onlyMethods(['run', 'init', 'getActionResult'])->getMock();
        $action->expects($this->once())->method('init');
        $action->expects($this->once())->method('run');
        $action->method('getActionResult')->willReturn(new ActionResult($affected));
        $configuration = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()
            ->onlyMethods(['getActionById'])->getMock();
        $configuration->method('getActionById')->with('task-id')->willReturn($action);
        $controller = $this->getMockBuilder(d3ActionWizard::class)->onlyMethods(['getSettingsService'])->getMock();
        $controller->method('getSettingsService')->willReturn($settings);
        $this->setValue($controller, 'configuration', $configuration);

        Registry::set(Request::class, $request);
        Registry::set(Language::class, $language);
        Registry::set('logger', $logger);
        Registry::set(UtilsView::class, $utilsView);
        try {
            $controller->runTask();
            $this->assertSame($expectedMessage, $controller->getActionSuccessMessage());
        } finally {
            Registry::set(Request::class, $oldRequest);
            Registry::set(Language::class, $oldLanguage);
            Registry::set('logger', $oldLogger);
            Registry::set(UtilsView::class, $oldUtilsView);
        }
    }

    public function successfulActionResults(): array
    {
        return [
            'zero rows' => [0, 'D3_DATAWIZARD_ERR_ACTIONRESULTS', 'TestTitle - 0 affected'],
            'one row' => [1, 'D3_DATAWIZARD_ERR_ACTIONRESULT', 'TestTitle - 1 affected'],
            'multiple rows' => [3, 'D3_DATAWIZARD_ERR_ACTIONRESULTS', 'TestTitle - 3 affected'],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::runTask
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ActionWizard::getActionSuccessMessage
     */
    public function testRunTaskClearsPreviousSuccessBeforeAnError(): void
    {
        $controller = $this->getMockBuilder(d3ActionWizard::class)->onlyMethods(['execute'])->getMock();
        $controller->method('execute')->willThrowException(new DataWizardException('failed', []));
        $this->setValue($controller, 'actionSuccessMessage', 'old success');

        $oldLogger = Registry::getLogger();
        $oldUtilsView = Registry::getUtilsView();
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('error')->with('failed');
        $utilsView = $this->getMockBuilder(UtilsView::class)->onlyMethods(['addErrorToDisplay'])->getMock();
        $utilsView->expects($this->once())->method('addErrorToDisplay');
        Registry::set('logger', $logger);
        Registry::set(UtilsView::class, $utilsView);
        try {
            $controller->runTask();
            $this->assertNull($controller->getActionSuccessMessage());
        } finally {
            Registry::set('logger', $oldLogger);
            Registry::set(UtilsView::class, $oldUtilsView);
        }
    }

    /**
     * @return array
     */
    public function runTaskFailedDataProvider(): array
    {
        return [
            [DataWizardException::class, ['exc_msg', []]],
            [DBALException::class, ['exc_msg', 20, new Exception()]],
        ];
    }
}
