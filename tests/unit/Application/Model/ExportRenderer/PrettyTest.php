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

namespace D3\DataWizard\tests\unit\Application\Model\ExportRenderer;

use D3\DataWizard\Application\Model\ExportRenderer\Pretty;
use MathieuViossat\Util\ArrayToTextTable;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class PrettyTest extends ExportRendererTest
{
    /** @var Pretty */
    protected $_oModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oModel = oxNew(Pretty::class);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Pretty::getContent
     * @test
     * @throws ReflectionException
     */
    public function canGetContent()
    {
        $expected = 'expectedReturn';
        $fieldList = ['field1', 'field2'];
        $valueList = ['value1', 'value2'];

        /** @var ArrayToTextTable|MockObject $csvMock */
        $arrayToTextTableMock = $this->getMockBuilder(ArrayToTextTable::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getTable'])
            ->getMock();
        $arrayToTextTableMock->expects($this->atLeastOnce())->method('getTable')->willReturn($expected);

        /** @var Pretty|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(Pretty::class)
            ->onlyMethods(['getArrayToTextTableInstance'])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getArrayToTextTableInstance')->willReturn($arrayToTextTableMock);
        $this->_oModel = $modelMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oModel,
                'getContent',
                [$valueList, $fieldList]
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Pretty::getArrayToTextTableInstance
     * @test
     * @throws ReflectionException
     */
    public function canGetArrayToTextTableInstance()
    {
        $this->assertInstanceOf(
            ArrayToTextTable::class,
            $this->callMethod(
                $this->_oModel,
                'getArrayToTextTableInstance',
                [['field1', 'field2']]
            )
        );
    }
}