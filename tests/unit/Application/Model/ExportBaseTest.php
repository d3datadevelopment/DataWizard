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

use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use FormManager\Inputs\Hidden;
use FormManager\Inputs\Number;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\Exception\StandardException;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ExportBaseTest extends d3ModCfgUnitTestCase
{
    /** @var d3TestExport */
    protected $_oModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3TestExport::class);
    }

    public function tearDown() : void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getDescription
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
     * @covers \D3\DataWizard\Application\Model\ExportBase::getButtonText
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
     * @covers \D3\DataWizard\Application\Model\ExportBase::hasFormElements
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

    public function canGetHasFormElementsDataProvider()
    {
        return [
            'hasFormElements'   => [['abc', 'def'], true],
            'hasNoFormElements' => [[], false],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getFormElements
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
     * @covers \D3\DataWizard\Application\Model\ExportBase::registerFormElement
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
                'setAttribute'
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
            'Hidden' => [Hidden::class]
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::run
     * @test
     * @throws ReflectionException
     */
    public function canRunWithoutFormElements()
    {
        $format = 'myFormat';
        $path = 'myPath';

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'hasFormElements',
                'executeExport'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('hasFormElements')->willReturn(false);
        $modelMock->expects($this->atLeastOnce())->method('executeExport')->with($format, $path)->willReturn('some content');
        $this->_oModel = $modelMock;

        $this->callMethod(
            $this->_oModel,
            'run',
            [$format, $path]
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::run
     * @test
     * @throws ReflectionException
     * @dataProvider canRunWithFormElementsDataProvider
     */
    public function canRunWithFormElements($elements, $blThrowException)
    {
        $format = 'myFormat';
        $path = 'myPath';

        $expectedException = oxNew(StandardException::class);

        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'hasFormElements',
                'executeExport',
                'getFormElements'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('hasFormElements')->willReturn(true);
        $modelMock->expects($this->exactly((int) !$blThrowException))->method('executeExport')->with($format, $path)->willReturn('some content');
        $modelMock->expects($this->atLeastOnce())->method('getFormElements')->willReturn($elements);
        $this->_oModel = $modelMock;

        if ($blThrowException) {
            $this->expectException(get_class($expectedException));
        }

        $this->callMethod(
            $this->_oModel,
            'run',
            [$format, $path]
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
            'invalidElements' => [[$validMock, $invalidField], true]
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::executeExport
     * @test
     * @throws ReflectionException
     * @dataProvider canExecuteExportDataProvider
     */
    public function canExecuteExport($path, $throwsException)
    {
        /** @var d3filesystem|MockObject $fsMock */
        $fsMock = $this->getMockBuilder(d3filesystem::class)
            ->onlyMethods([
                'startDirectDownload',
                'filterFilename',
                'trailingslashit',
                'createFile'
            ])
            ->getMock();
        $fsMock->expects($this->exactly((int) !isset($path)))->method('startDirectDownload')->willReturn(true);
        $fsMock->method('filterFilename')->willReturnArgument(0);
        $fsMock->expects($this->exactly((int) isset($path)))->method('trailingslashit')->willReturnArgument(0);
        $fsMock->expects($this->exactly((int) isset($path)))->method('createFile')->willReturn((bool) !$throwsException);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getQuery',
                'getExportData',
                'renderContent',
                'getFileSystem',
                'getExportFileName'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getQuery')->willReturn(['SELECT 1', ['arg1', 'arg2']]);
        $modelMock->expects($this->atLeastOnce())->method('getExportData')->willReturn([[1, 2], ['field1', 'field2']]);
        $modelMock->expects($this->atLeastOnce())->method('renderContent')->willReturn('some content');
        $modelMock->expects($this->atLeastOnce())->method('getFileSystem')->willReturn($fsMock);
        $modelMock->expects($this->atLeastOnce())->method('getExportFileName')->willReturn('exportFileName');

        $this->_oModel = $modelMock;

        if ($path && $throwsException) {
            $this->expectException(ExportFileException::class);
        }

        $this->callMethod(
            $this->_oModel,
            'executeExport',
            ['CSV', $path]
        );
    }

    /**
     * @return array[]
     */
    public function canExecuteExportDataProvider(): array
    {
        return [
            'unable to create path for saving file'   => ['myPath', true],
            'can create path for saving file'   => ['myPath', false],
            'no path for download'   => [null, false],
        ];
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::d3GetDb
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

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getFileSystem
     * @test
     * @throws ReflectionException
     */
    public function canGetFileSystem()
    {
        $this->assertInstanceOf(
            d3filesystem::class,
            $this->callMethod(
                $this->_oModel,
                'getFileSystem'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getRenderer
     * @test
     * @throws ReflectionException
     */
    public function canGetRenderer()
    {
        /** @var RendererInterface|MockObject $rendererMock */
        $rendererMock = $this->getMockBuilder(Csv::class)
            ->getMock();

        /** @var RendererBridge|MockObject $rendererBridgeMock */
        $rendererBridgeMock = $this->getMockBuilder(RendererBridge::class)
            ->onlyMethods(['getRenderer'])
            ->getMock();
        $rendererBridgeMock->expects($this->atLeastOnce())->method('getRenderer')->willReturn($rendererMock);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getRendererBridge'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getRendererBridge')->willReturn($rendererBridgeMock);

        $this->_oModel = $modelMock;

        $this->assertSame(
            $rendererMock,
            $this->callMethod(
                $this->_oModel,
                'getRenderer',
                [RendererBridge::FORMAT_CSV]
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getRendererBridge
     * @test
     * @throws ReflectionException
     */
    public function canGetRendererBridge()
    {
        $this->assertInstanceOf(
            RendererBridge::class,
            $this->callMethod(
                $this->_oModel,
                'getRendererBridge'
            )
        );
    }



    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getFileExtension
     * @test
     * @throws ReflectionException
     */
    public function canGetFileExtension()
    {
        $format = RendererBridge::FORMAT_CSV;
        $expected = 'myFileExtension';

        /** @var RendererInterface|MockObject $rendererMock */
        $rendererMock = $this->getMockBuilder(Csv::class)
            ->onlyMethods(['getFileExtension'])
            ->getMock();
        $rendererMock->expects($this->atLeastOnce())->method('getFileExtension')->willReturn($expected);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getRenderer'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getRenderer')->with($format)->willReturn($rendererMock);

        $this->_oModel = $modelMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oModel,
                'getFileExtension',
                [$format]
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::renderContent
     * @test
     * @throws ReflectionException
     */
    public function canRenderContent()
    {
        $rows = ['row1', 'row2'];
        $fieldnames = ['fieldname1', 'fieldname2'];
        $format = RendererBridge::FORMAT_CSV;
        $expected = 'myContent';

        /** @var RendererInterface|MockObject $rendererMock */
        $rendererMock = $this->getMockBuilder(Csv::class)
            ->onlyMethods(['getContent'])
            ->getMock();
        $rendererMock->expects($this->atLeastOnce())->method('getContent')->with($rows, $fieldnames)->willReturn($expected);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getRenderer'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getRenderer')->with($format)->willReturn($rendererMock);

        $this->_oModel = $modelMock;

        $this->assertSame(
            $expected,
            $this->callMethod(
                $this->_oModel,
                'renderContent',
                [$rows, $fieldnames, $format]
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getExportFilenameBase
     * @test
     * @throws ReflectionException
     */
    public function canGetExportFilenameBase()
    {
        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getTitle'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getTitle')->willReturn('someTitle');

        $this->_oModel = $modelMock;

        $this->callMethod(
            $this->_oModel,
            'getExportFilenameBase'
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getExportFileName
     * @test
     * @throws ReflectionException
     */
    public function canGetExportFileName()
    {
        $format = RendererBridge::FORMAT_CSV;

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getExportFilenameBase',
                'getFileExtension'
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getExportFilenameBase')->willReturn('base');
        $modelMock->expects($this->atLeastOnce())->method('getFileExtension')->with($format)->willReturn('extension');

        $this->_oModel = $modelMock;

        $this->assertRegExp(
            '/^base_(\d{4})-(\d{2})-(\d{2})_(\d{2})-(\d{2})-(\d{2})\.extension$/m',
            $this->callMethod(
                $this->_oModel,
                'getExportFileName',
                [$format]
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getExportData
     * @test
     * @throws ReflectionException
     * @dataProvider canGetExportDataDataProvider
     */
    public function canGetExportData($query, $throwsException, $dbResult)
    {
        /** @var Database|MockObject $dbMock */
        $dbMock = $this->getMockBuilder(Database::class)
            ->onlyMethods(['getAll'])
            ->getMock();
        $dbMock->expects($this->exactly((int) !$throwsException))->method('getAll')->willReturn($dbResult);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'd3GetDb'
            ])
            ->getMock();
        $modelMock->expects($this->exactly((int) !$throwsException))->method('d3GetDb')->willReturn($dbMock);

        $this->_oModel = $modelMock;

        try {
            $result = $this->callMethod(
                $this->_oModel,
                'getExportData',
                [[$query], ['param1', 'param2']]
            );

            $this->assertSame(
                [
                    [
                        [
                            'field1'    => 'content1',
                            'field2'    => 'content2'
                        ]
                    ],
                    [
                        'field1',
                        'field2'
                    ]
                ],
                $result
            );
        } catch (TaskException $e) {
            if ($throwsException) {
                $this->assertStringContainsString('NOEXPORTSELECT', $e->getMessage());
            } elseif (!count($dbResult)) {
                $this->assertStringContainsString('kein Inhalt', $e->getMessage());
            }
        }
    }

    /**
     * @return array[]
     */
    public function canGetExportDataDataProvider(): array
    {
        return [
            'not SELECT throws exception'   => [' UPDATE 1', true, []],
            'empty SELECT'   => [' SELECT 1', false, []],
            'fulfilled SELECT'   => [' SELECT 1', false, [['field1' => 'content1', 'field2' => 'content2']]],
        ];
    }
}