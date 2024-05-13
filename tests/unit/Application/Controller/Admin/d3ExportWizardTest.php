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

use D3\DataWizard\Application\Controller\Admin\d3ExportWizard;
use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Constants;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

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
     * @covers \D3\DataWizard\Application\Controller\Admin\d3ExportWizard::getGroupTasks()
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
        $requestMock->expects($this->any())->method('getRequestEscapedParameter')->willReturnCallback([$this, 'executePassRequestCallback']);
        //OnConsecutiveCalls('testTaskId', 'CSV');
        Registry::set(Request::class, $requestMock);

        /** @var ModuleSettingService $settingsServiceMock */
        $settingsServiceMock = $this->getMockBuilder(ModuleSettingService::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBoolean'])
            ->getMock();
        $settingsServiceMock->expects($this->once())->method('getBoolean')->with(
            $this->identicalTo('d3datawizard_debug'),
            $this->identicalTo(Constants::OXID_MODULE_ID)
        )->willReturn($blDebug);

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



    /**
     * @return string[][]
     */
    public function runTaskFailedDataProvider(): array
    {
        return [
            [DataWizardException::class],
            [DBALException::class],
            [DatabaseErrorException::class],
        ];
    }
}
