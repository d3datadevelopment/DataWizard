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

namespace D3\DataWizard\tests\unit\Application\Model;

use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ConfigurationTest extends d3ModCfgUnitTestCase
{
    /** @var Configuration */
    protected $_oModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(Configuration::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::__construct
     * @test
     * @throws ReflectionException
     */
    public function canConstruct()
    {
        /** @var Configuration|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Configuration::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['configure'])
            ->getMock();
        $modelMock->expects($this->once())->method('configure');
        $this->_oModel = $modelMock;

        $this->callMethod(
            $this->_oModel,
            '__construct'
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::configure()
     * @test
     * @throws ReflectionException
     */
    public function canConfigure()
    {
        $this->assertEmpty(
            $this->callMethod(
                $this->_oModel,
                'configure'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::registerAction
     * @test
     * @throws ReflectionException
     */
    public function canRegisterAction()
    {
        $action = oxNew(d3TestAction::class);

        $oldCount = count($this->getValue(
            $this->_oModel,
            'actions'
        ));

        $this->callMethod(
            $this->_oModel,
            'registerAction',
            ['testGroup', $action]
        );

        $actions = $this->getValue(
            $this->_oModel,
            'actions'
        );

        $newCount = count($actions);

        $flattedActions = $this->array_flatten($actions);

        $this->assertGreaterThan($oldCount, $newCount);
        $this->assertContains($action, $flattedActions);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::registerExport
     * @test
     * @throws ReflectionException
     */
    public function canRegisterExport()
    {
        $export = oxNew(d3TestExport::class);

        $oldCount = count($this->getValue(
            $this->_oModel,
            'exports'
        ));

        $this->callMethod(
            $this->_oModel,
            'registerExport',
            ['testGroup', $export]
        );

        $exports = $this->getValue(
            $this->_oModel,
            'exports'
        );

        $newCount = count($exports);

        $flattedExports = $this->array_flatten($exports);

        $this->assertGreaterThan($oldCount, $newCount);
        $this->assertContains($export, $flattedExports);
    }

    /**
     * @param $array
     * @return array|false
     */
    public function array_flatten($array)
    {
        if (!is_array($array)) {
            return false;
        }
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->array_flatten($value));
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getGroupedActions()
     * @test
     * @throws ReflectionException
     */
    public function canGetGroupedActions()
    {
        $actionList = ['abc', 'def'];

        $this->setValue(
            $this->_oModel,
            'actions',
            $actionList
        );

        $this->assertSame(
            $actionList,
            $this->callMethod(
                $this->_oModel,
                'getGroupedActions'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getGroupedExports()
     * @test
     * @throws ReflectionException
     */
    public function canGetGroupedExports()
    {
        $exportList = ['abc', 'def'];

        $this->setValue(
            $this->_oModel,
            'exports',
            $exportList
        );

        $this->assertSame(
            $exportList,
            $this->callMethod(
                $this->_oModel,
                'getGroupedExports'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getActionGroups()
     * @test
     * @throws ReflectionException
     */
    public function canGetActionGroups()
    {
        $actionList = ['abc'    => '123', 'def' => '456'];

        $this->setValue(
            $this->_oModel,
            'actions',
            $actionList
        );

        $this->assertSame(
            ['abc', 'def'],
            $this->callMethod(
                $this->_oModel,
                'getActionGroups'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getExportGroups()
     * @test
     * @throws ReflectionException
     */
    public function canGetExportGroups()
    {
        $exportList = ['abc'    => '123', 'def' => '456'];

        $this->setValue(
            $this->_oModel,
            'exports',
            $exportList
        );

        $this->assertSame(
            ['abc', 'def'],
            $this->callMethod(
                $this->_oModel,
                'getExportGroups'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getActionsByGroup()
     * @test
     * @throws ReflectionException
     */
    public function canGetActionsByGroup()
    {
        $actionList = ['abc' => ['123'], 'def' => ['456']];

        $this->setValue(
            $this->_oModel,
            'actions',
            $actionList
        );

        $this->assertSame(
            ['456'],
            $this->callMethod(
                $this->_oModel,
                'getActionsByGroup',
                ['def']
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getExportsByGroup()
     * @test
     * @throws ReflectionException
     */
    public function canGetExportsByGroup()
    {
        $exportList = ['abc' => ['123'], 'def' => ['456']];

        $this->setValue(
            $this->_oModel,
            'exports',
            $exportList
        );

        $this->assertSame(
            ['456'],
            $this->callMethod(
                $this->_oModel,
                'getExportsByGroup',
                ['def']
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getAllActions()
     * @test
     * @throws ReflectionException
     */
    public function canGetAllActions()
    {
        /** @var Configuration|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods([
                'getActionGroups',
                'getActionsByGroup',
            ])
            ->getMock();
        $modelMock->expects($this->once())->method('getActionGroups')->willReturn(['123', '456']);
        $modelMock->expects($this->exactly(2))->method('getActionsByGroup')->willReturnOnConsecutiveCalls(
            ['123', '456'],
            ['789', '012']
        );

        $this->_oModel = $modelMock;

        $this->assertSame(
            ['123','456', '789', '012'],
            $this->callMethod(
                $this->_oModel,
                'getAllActions'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getAllExports()
     * @test
     * @throws ReflectionException
     */
    public function canGetAllExports()
    {
        /** @var Configuration|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods([
                'getExportGroups',
                'getExportsByGroup',
            ])
            ->getMock();
        $modelMock->expects($this->once())->method('getExportGroups')->willReturn(['123', '456']);
        $modelMock->expects($this->exactly(2))->method('getExportsByGroup')->willReturnOnConsecutiveCalls(
            ['123', '456'],
            ['789', '012']
        );

        $this->_oModel = $modelMock;

        $this->assertSame(
            ['123', '456', '789', '012'],
            $this->callMethod(
                $this->_oModel,
                'getAllExports'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getActionById()
     * @test
     * @throws ReflectionException
     * @dataProvider canGetActionByIdDataProvider
     */
    public function canGetActionById($id, $throwException)
    {
        $expected = oxNew(d3TestAction::class);

        /** @var Configuration|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getAllActions'])
            ->getMock();
        $modelMock->expects($this->once())->method('getAllActions')->willReturn(['123'  => oxNew(d3TestAction::class), '456' => $expected]);
        $this->_oModel = $modelMock;

        if ($throwException) {
            $this->expectException(DataWizardException::class);
        }

        $return = $this->callMethod(
            $this->_oModel,
            'getActionById',
            [$id]
        );

        if (!$throwException) {
            $this->assertSame(
                $expected,
                $return
            );
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Configuration::getExportById()
     * @test
     * @throws ReflectionException
     * @dataProvider canGetActionByIdDataProvider
     */
    public function canGetExportById($id, $throwException)
    {
        $expected = oxNew(d3TestExport::class);

        /** @var Configuration|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Configuration::class)
            ->onlyMethods(['getAllExports'])
            ->getMock();
        $modelMock->expects($this->once())->method('getAllExports')->willReturn(['123'  => oxNew(d3TestExport::class), '456' => $expected]);
        $this->_oModel = $modelMock;

        if ($throwException) {
            $this->expectException(DataWizardException::class);
        }

        $return = $this->callMethod(
            $this->_oModel,
            'getExportById',
            [$id]
        );

        if (!$throwException) {
            $this->assertSame(
                $expected,
                $return
            );
        }
    }

    /**
     * @return array[]
     */
    public function canGetActionByIdDataProvider(): array
    {
        return [
            'unset id'  => ['987', true],
            'set id'  => ['456', false],
        ];
    }
}
