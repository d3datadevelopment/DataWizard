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

use D3\DataWizard\Application\Model\ExportRenderer\Json;

use D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv;

use D3\DataWizard\Application\Model\ExportRenderer\Csv;

use D3\DataWizard\Application\Controller\Admin\d3ExportWizard;
use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Constants;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use Doctrine\DBAL\Exception as DBALException;
use Exception;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class d3ExportWizardTest extends d3AdminController
{
    /** @var d3ExportWizard */
    protected $_oController;

    protected $testClassName = d3ExportWizard::class;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oController = oxNew($this->testClassName);
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::getGroups()
     * @test
     * @throws ReflectionException
     */
    public function canGetGroups()
    {
        $this->assertControllerMethodPublic('getGroups');
        $expected = ['expected' => 'array'];
        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getExportGroups'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportGroups')->willReturn($expected);

        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->assertSame(
            $expected,
            $this->_oController->getGroups()
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::getGroupTasks()
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
            ->onlyMethods(['getExportsByGroup'])
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportsByGroup')->with($argument)->willReturn($expected);

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
        $requestMock->expects($this->any())->method('getRequestEscapedParameter')->willReturnCallback(
            static fn ($name) => $name === 'taskid' ? 'testTaskId' : ($blDebug ? 'query' : Csv::FORMAT)
        );
        //OnConsecutiveCalls('testTaskId', 'CSV');
        Registry::set(Request::class, $requestMock);

        /** @var ModuleSettingService $settingsServiceMock */
        $settingsServiceMock = $this->getMockBuilder(ModuleSettingService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBoolean'])
            ->getMock();
        $settingsServiceMock->expects($this->never())->method('getBoolean');

        /** @var d3ExportWizard|MockObject $controllerMock */
        $controllerMock = $this->getMockBuilder(d3ExportWizard::class)
            ->onlyMethods(['getSettingsService'])
            ->getMock();
        $controllerMock->method('getSettingsService')->willReturn($settingsServiceMock);
        $this->_oController = $controllerMock;

        /** @var d3TestAction|MockObject $exportMock */
        $exportMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getQuery',
                'run',
                'init',
            ])
            ->getMock();
        $exportMock->expects($this->exactly((int) $blDebug))->method('getQuery')->willReturn(['SELECT 1', ['1']]);
        $exportMock->expects($this->exactly((int) !$blDebug))->method('run')->willReturn('');
        $exportMock->expects($this->once())->method('init');

        /** @var Configuration|MockObject $configurationMock */
        $configurationMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getExportById'])
            ->disableOriginalConstructor()
            ->getMock();
        $configurationMock->expects($this->atLeastOnce())->method('getExportById')->with('testTaskId')->willReturn($exportMock);
        $this->setValue($this->_oController, 'configuration', $configurationMock);

        $this->callMethod(
            $this->_oController,
            'execute'
        );
        if ($blDebug) {
            $this->assertStringContainsString('SELECT 1', $this->_oController->getQueryPreview());
        } else {
            $this->assertNull($this->_oController->getQueryPreview());
        }
    }

    public function executePassRequestCallback($varName)
    {
        switch ($varName) {
            case 'taskid':
                return 'testTaskId';
            case 'format':
                return Csv::FORMAT;
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

    /**
     * @dataProvider adminExportFormats
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::execute
     */
    public function testAdminExportUsesRequestedOrSpreadsheetDefaultFormat($requested, string $expected): void
    {
        $oldRequest = Registry::getRequest();
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['getRequestEscapedParameter'])->getMock();
        $request->method('getRequestEscapedParameter')->willReturnCallback(
            static function ($name) use ($requested) {
                return $name === 'taskid' ? 'testTaskId' : $requested;
            }
        );
        $settings = $this->getMockBuilder(ModuleSettingService::class)->disableOriginalConstructor()
            ->onlyMethods(['getBoolean'])->getMock();
        $settings->method('getBoolean')->willReturn(false);
        $export = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['run', 'init'])->getMock();
        $export->expects($this->once())->method('init');
        $export->expects($this->once())->method('run')->with($expected)->willReturn('');
        $configuration = $this->getMockBuilder(Configuration::class)->disableOriginalConstructor()
            ->onlyMethods(['getExportById'])->getMock();
        $configuration->expects($this->once())->method('getExportById')->with('testTaskId')->willReturn($export);
        $controller = $this->getMockBuilder(d3ExportWizard::class)->onlyMethods(['getSettingsService'])->getMock();
        $controller->method('getSettingsService')->willReturn($settings);
        $this->setValue($controller, 'configuration', $configuration);

        Registry::set(Request::class, $request);
        try {
            $this->callMethod($controller, 'execute');
        } finally {
            Registry::set(Request::class, $oldRequest);
        }
    }

    public function adminExportFormats(): array
    {
        return [
            'missing format' => [null, SpreadsheetCsv::FORMAT],
            'empty format' => ['', SpreadsheetCsv::FORMAT],
            'spreadsheet CSV' => [SpreadsheetCsv::FORMAT, SpreadsheetCsv::FORMAT],
            'explicit raw CSV' => [Csv::FORMAT, Csv::FORMAT],
            'explicit JSON' => [Json::FORMAT, Json::FORMAT],
            'unknown format is passed through' => ['unknown', 'unknown'],
        ];
    }

    /**
     * @return array
     */
    public function runTaskFailedDataProvider(): array
    {
        return [
            [DataWizardException::class, ['exc_msg', []]],
            [DBALException::class, ['exc_msg', 20, new Exception()]],
            [DatabaseErrorException::class, ['exc_msg', 20, new Exception()]],
        ];
    }
}
