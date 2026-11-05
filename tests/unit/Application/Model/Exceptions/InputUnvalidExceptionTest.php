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

namespace D3\DataWizard\tests\unit\Application\Model\Exceptions;

use D3\DataWizard\Application\Model\Exceptions\InputUnvalidException;
use D3\DataWizard\Application\Model\ExportBase;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use Exception;
use FormManager\Inputs\Number;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class InputUnvalidExceptionTest extends d3ModCfgUnitTestCase
{
    /** @var InputUnvalidException */
    protected $_oModel;

    /**
     * @covers \D3\DataWizard\Application\Model\Exceptions\InputUnvalidException::__construct
     * @test
     * @throws ReflectionException
     */
    public function canConstruct()
    {
        $invalidField = new Number('Age', [
            'required' => true,
            'min' => 1,
            'max' => 10,
            'step' => 5,
        ]);
        $invalidField
            ->setValue(20)
            ->setErrorMessages(['errorMsgs']);

        /** @var ExportBase|MockObject $taskMock */
        $taskMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getTitle'])
            ->getMock();
        $taskMock->expects($this->atLeastOnce())->method('getTitle')->willReturn('testTitle');

        $this->_oModel = oxNew(InputUnvalidException::class, $taskMock, $invalidField);

        $this->assertMatchesRegularExpression(
            '/^testTitle\s-\sAge\s->\s.*\sless\s/m',
            $this->_oModel->getMessage()
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\Exceptions\InputUnvalidException::__construct
     * @test
     */
    public function canConstructWithoutLabel(): void
    {
        $invalidField = new Number(null, ['required' => true]);
        $invalidField->setValue(null)->setErrorMessages(['errorMsgs']);

        $task = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods(['getTitle'])
            ->getMock();
        $task->method('getTitle')->willReturn('testTitle');

        $exception = oxNew(InputUnvalidException::class, $task, $invalidField);

        $this->assertStringContainsString('testTitle -  -> ', $exception->getMessage());
        $this->assertNotSame('testTitle -  -> ', $exception->getMessage());
    }
}
