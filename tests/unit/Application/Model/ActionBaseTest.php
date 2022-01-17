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

use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\tests\tools\d3TestAction;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use FormManager\Inputs\Hidden;
use FormManager\Inputs\Number;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\Exception\StandardException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ActionBaseTest extends d3ModCfgUnitTestCase
{
    /** @var d3TestAction */
    protected $_oModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3TestAction::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::getDescription
     * @test
     * @throws ReflectionException
     */
    public function canGetDescription()
    {
        $this->assertIsString(
            $this->callMethod(
                $this->_oModel,
                'getDescription'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::getButtonText
     * @test
     * @throws ReflectionException
     */
    public function canGetButtonText()
    {
        $this->assertIsString(
            $this->callMethod(
                $this->_oModel,
                'getButtonText'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::hasFormElements
     * @test
     * @throws ReflectionException
     * @dataProvider canGetHasFormElementsDataProvider
     */
    public function canGetHasFormElements($formElements, $expected)
    {
        $this->setValue($this->_oModel, 'formElements', $formElements);

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oModel,
                'hasFormElements'
            )
        );
    }

    public function canGetHasFormElementsDataProvider(): array
    {
        return [
            'hasFormElements'   => [['abc', 'def'], true],
            'hasNoFormElements' => [[], false],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::getFormElements
     * @test
     * @throws ReflectionException
     * @dataProvider canGetHasFormElementsDataProvider
     */
    public function canGetFormElements($formElements)
    {
        $this->setValue($this->_oModel, 'formElements', $formElements);

        $this->assertSame(
            $formElements,
            $this->callMethod(
                $this->_oModel,
                'getFormElements'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::registerFormElement
     * @test
     * @throws ReflectionException
     * @dataProvider canRegisterFormElementDataProvider
     */
    public function canRegisterFormElement($inputClass)
    {
        $oldCount = count($this->getValue($this->_oModel, 'formElements'));

        /** @var Radio|MockObject $inputMock */
        $inputMock = $this->getMockBuilder($inputClass)
            ->onlyMethods([
                'setTemplate',
                'setAttribute',
            ])
            ->getMock();
        $inputMock->expects($this->atLeastOnce())->method('setTemplate');
        $inputMock->expects($this->atLeastOnce())->method('setAttribute');

        $this->callMethod(
            $this->_oModel,
            'registerFormElement',
            [$inputMock]
        );

        $newCount = count($this->getValue($this->_oModel, 'formElements'));

        $this->assertGreaterThan($oldCount, $newCount);
    }

    /**
     * @return \string[][]
     */
    public function canRegisterFormElementDataProvider(): array
    {
        return [
            'Radio' => [Radio::class],
            'Checkbox' => [Radio::class],
            'Hidden' => [Hidden::class],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::run
     * @test
     * @throws ReflectionException
     */
    public function canRunWithoutFormElements()
    {
        $modelMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods([
                'hasFormElements',
                'executeAction',
                'getQuery',
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('hasFormElements')->willReturn(false);
        $modelMock->expects($this->atLeastOnce())->method('executeAction')->willReturn(1);
        $modelMock->expects($this->atLeastOnce())->method('getQuery')->willReturn([]);
        $this->_oModel = $modelMock;

        $this->callMethod(
            $this->_oModel,
            'run'
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::run
     * @test
     * @throws ReflectionException
     * @dataProvider canRunWithFormElementsDataProvider
     */
    public function canRunWithFormElements($elements, $blThrowException)
    {
        $expectedException = oxNew(StandardException::class);

        $modelMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods([
                'hasFormElements',
                'executeAction',
                'getQuery',
                'getFormElements',
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('hasFormElements')->willReturn(true);
        $modelMock->expects($this->exactly((int) !$blThrowException))->method('executeAction')->willReturn(1);
        $modelMock->expects($this->exactly((int) !$blThrowException))->method('getQuery')->willReturn([]);
        $modelMock->expects($this->atLeastOnce())->method('getFormElements')->willReturn($elements);
        $this->_oModel = $modelMock;

        if ($blThrowException) {
            $this->expectException(get_class($expectedException));
        }

        $this->callMethod(
            $this->_oModel,
            'run'
        );
    }

    /**
     * @return array[]
     */
    public function canRunWithFormElementsDataProvider(): array
    {
        /** @var Radio|MockObject $validMock */
        $validMock = $this->getMockBuilder(Radio::class)
            ->onlyMethods(['isValid'])
            ->getMock();
        $validMock->expects($this->atLeastOnce())->method('isValid')->willReturn(true);

        $invalidField = new Number(null, [
            'required' => true,
            'min' => 1,
            'max' => 10,
            'step' => 5,
        ]);
        $invalidField
            ->setValue(20)
            ->setErrorMessages(['errorMsgs']);

        return [
            'validElements' => [[$validMock, $validMock], false],
            'invalidElements' => [[$validMock, $invalidField], true],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::executeAction
     * @test
     * @throws ReflectionException
     * @dataProvider canExecuteActionDataProvider
     */
    public function canExecuteAction($query, $throwsException)
    {
        /** @var Database|MockObject $dbMock */
        $dbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods(['execute'])
            ->getMock();
        $dbMock->expects($this->exactly((int) !$throwsException))->method('execute')->willReturn(true);

        /** @var d3TestAction|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestAction::class)
            ->onlyMethods(['d3GetDb'])
            ->getMock();
        $modelMock->expects($this->exactly((int) !$throwsException))->method('d3GetDb')->willReturn($dbMock);

        $this->_oModel = $modelMock;

        try {
            $this->callMethod(
                $this->_oModel,
                'executeAction',
                [[$query, ['parameters']]]
            );
        } catch (TaskException $e) {
            if ($throwsException) {
                $this->assertStringContainsString('ACTIONSELECT', $e->getMessage());
            } else {
                $this->assertStringContainsString('ACTIONRESULT', $e->getMessage());
            }
        }
    }

    /**
     * @return array[]
     */
    public function canExecuteActionDataProvider(): array
    {
        return [
            'Select throws exception'   => ['SELECT 1', true],
            'Update dont throws exception'   => ['UPDATE 1', false],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ActionBase::d3GetDb
     * @test
     * @throws ReflectionException
     */
    public function canGetDb()
    {
        $this->assertInstanceOf(
            Database::class,
            $this->callMethod(
                $this->_oModel,
                'd3GetDb'
            )
        );
    }
}
