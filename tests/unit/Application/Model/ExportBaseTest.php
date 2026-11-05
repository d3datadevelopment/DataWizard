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

use Assert\LazyAssertionException;
use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\DataWizard\tests\tools\d3TestExport;
use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Application\Model\download;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Result;
use FormManager\Inputs\Checkbox;
use FormManager\Inputs\Hidden;
use FormManager\Inputs\Input;
use FormManager\Inputs\Number;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Database\Adapter\Doctrine\Database;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ExportBaseTest extends d3ModCfgUnitTestCase
{
    /** @var d3TestExport */
    protected $_oModel;

    public function setUp(): void
    {
        parent::setUp();

        $this->_oModel = oxNew(d3TestExport::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::init
     * @test
     */
    public function canInitializeExport(): void
    {
        $this->assertNull($this->_oModel->init());
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getDownload
     * @test
     * @throws ReflectionException
     */
    public function canGetDownloadService(): void
    {
        $this->assertInstanceOf(
            download::class,
            $this->callMethod($this->_oModel, 'getDownload')
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getBoxTemplate
     * @test
     */
    public function canGetBoxTemplate(): void
    {
        $this->assertStringStartsWith(
            '@d3datawizard/admin/inc/wizardDefaultBox.',
            $this->_oModel->getBoxTemplate()
        );
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

    public function canGetHasFormElementsDataProvider(): array
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
    public function canRegisterFormElement($inputClass, $expectedTemplate, $expectedClass)
    {
        $oldCount = count($this->getValue($this->_oModel, 'formElements'));

        /** @var Input|MockObject $inputMock */
        $inputMock = $this->getMockBuilder($inputClass)
            ->onlyMethods([
                'setTemplate',
                'setAttribute',
            ])
            ->getMock();
        $inputMock->expects($this->once())->method('setTemplate')->with($expectedTemplate);
        $inputMock->expects($this->once())->method('setAttribute')->with('class', $expectedClass);

        $this->callMethod(
            $this->_oModel,
            'registerFormElement',
            [$inputMock]
        );

        $newCount = count($this->getValue($this->_oModel, 'formElements'));

        $this->assertSame($oldCount + 1, $newCount);
        $this->assertSame($inputMock, $this->_oModel->getFormElements()[$oldCount]);
    }

    /**
     * @return \string[][]
     */
    public function canRegisterFormElementDataProvider(): array
    {
        return [
            'Radio' => [Radio::class, '<p class="form-check">{{ input }} {{ label }}</p>', 'form-check-input'],
            'Checkbox' => [Checkbox::class, '<p class="form-check">{{ input }} {{ label }}</p>', 'form-check-input'],
            'Hidden' => [Hidden::class, '<p class="formElements">{{ label }} {{ input }}</p>', 'form-control'],
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
                'executeExport',
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

        $expectedException = oxNew(LazyAssertionException::class, 'message', []);

        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'hasFormElements',
                'executeExport',
                'getFormElements',
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
            'invalidElements' => [[$validMock, $invalidField], true],
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
                'filterFilename',
                'trailingslashit',
            ])
            ->getMock();
        $fsMock->method('filterFilename')->willReturnArgument(0);
        $fsMock->expects($this->exactly((int) isset($path)))->method('trailingslashit')->willReturnArgument(0);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getContent',
                'getFileSystem',
                'getExportFileName',
                'getDownload',
                'saveExportFile',
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getContent')->willReturn('some content');
        $modelMock->expects($this->atLeastOnce())->method('getFileSystem')->willReturn($fsMock);
        $modelMock->expects($this->atLeastOnce())->method('getExportFileName')->willReturn('exportFileName');
        if (isset($path)) {
            $save = $modelMock->expects($this->once())->method('saveExportFile')
                ->with('myPathexportFileName', 'some content');
            if ($throwsException) {
                $save->willThrowException(oxNew(ExportFileException::class, 'myPathexportFileName'));
            } else {
                $save->willReturn('myPathexportFileName');
            }
        } else {
            $modelMock->expects($this->never())->method('saveExportFile');
        }
        if (is_null($path)) {
            $downloadMock = $this->getMockBuilder(download::class)
                ->onlyMethods(['startDownload'])
                ->getMock();
            $downloadMock->expects($this->once())
                ->method('startDownload')
                ->with('exportFileName', 'some content');
            $modelMock->expects($this->once())->method('getDownload')->willReturn($downloadMock);
        }

        $this->_oModel = $modelMock;

        if ($path && $throwsException) {
            $this->expectException(ExportFileException::class);
        }

        $result = $this->callMethod(
            $this->_oModel,
            'executeExport',
            ['CSV', $path]
        );
        $this->assertSame(isset($path) ? 'myPathexportFileName' : '', $result);
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
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportPreservesExistingFileOnCollision(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'd3export_');
        $this->assertNotFalse($path);
        file_put_contents($path, 'existing');
        $createdPath = null;

        try {
            $createdPath = $this->callMethod($this->_oModel, 'saveExportFile', [$path, 'new export']);
            $this->assertNotSame($path, $createdPath);
            $this->assertSame('existing', file_get_contents($path));
            $this->assertSame('new export', file_get_contents($createdPath));
        } finally {
            if ($createdPath !== null && is_file($createdPath)) {
                unlink($createdPath);
            }
            unlink($path);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportReportsUnwritableTarget(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'd3export_');
        $this->assertNotFalse($path);

        try {
            $this->expectException(ExportFileException::class);
            $this->callMethod($this->_oModel, 'saveExportFile', [$path . '/export.csv', 'content']);
        } finally {
            unlink($path);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportRejectsRemoteStream(): void
    {
        $this->expectException(ExportFileException::class);
        $this->callMethod($this->_oModel, 'saveExportFile', ['https://example.invalid/export.csv', 'content']);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportRemovesIncompleteFileOnWriteFailure(): void
    {
        $scheme = 'd3exportwritefailure';
        $this->assertTrue(stream_wrapper_register($scheme, FailingExportStream::class));
        FailingExportStream::$mode = 'write';
        FailingExportStream::$removedPaths = [];

        try {
            try {
                $this->callMethod($this->_oModel, 'saveExportFile', [$scheme . '://export.csv', 'content']);
                $this->fail('A write failure must raise an export exception.');
            } catch (ExportFileException $exception) {
                $this->assertSame([$scheme . '://export.csv'], FailingExportStream::$removedPaths);
            }
        } finally {
            stream_wrapper_unregister($scheme);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportHandlesPartialWrites(): void
    {
        $scheme = 'd3exportpartial';
        $this->assertTrue(stream_wrapper_register($scheme, FailingExportStream::class));
        FailingExportStream::$mode = 'partial';
        FailingExportStream::$writtenContent = '';

        $fsMock = $this->getMockBuilder(d3filesystem::class)->onlyMethods(['chmod'])->getMock();
        $fsMock->expects($this->once())->method('chmod')->with($scheme . '://export.csv')->willReturn(true);
        $modelMock = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['getFileSystem'])->getMock();
        $modelMock->method('getFileSystem')->willReturn($fsMock);

        try {
            $result = $this->callMethod($modelMock, 'saveExportFile', [$scheme . '://export.csv', 'content']);
            $this->assertSame($scheme . '://export.csv', $result);
            $this->assertSame('content', FailingExportStream::$writtenContent);
        } finally {
            stream_wrapper_unregister($scheme);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportRemovesFileWhenClosingFails(): void
    {
        $scheme = 'd3exportclosefailure';
        $this->assertTrue(stream_wrapper_register($scheme, FailingExportStream::class));
        FailingExportStream::$mode = 'partial';
        FailingExportStream::$removedPaths = [];

        $modelMock = $this->getMockBuilder(d3TestExport::class)->onlyMethods(['closeExportFile'])->getMock();
        $modelMock->expects($this->once())->method('closeExportFile')->willReturnCallback(
            static function ($handle): bool {
                fclose($handle);
                return false;
            }
        );

        try {
            try {
                $this->callMethod($modelMock, 'saveExportFile', [$scheme . '://export.csv', 'content']);
                $this->fail('A close failure must raise an export exception.');
            } catch (ExportFileException $exception) {
                $this->assertSame([$scheme . '://export.csv'], FailingExportStream::$removedPaths);
            }
        } finally {
            stream_wrapper_unregister($scheme);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::closeExportFile
     */
    public function testCloseExportFileClosesStream(): void
    {
        $handle = fopen('php://temp', 'w+b');
        $this->assertNotFalse($handle);
        $this->assertTrue($this->callMethod($this->_oModel, 'closeExportFile', [$handle]));
        $this->assertFalse(is_resource($handle));
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::saveExportFile
     */
    public function testFileExportStopsAfterRepeatedCollisions(): void
    {
        $scheme = 'd3exportcollision';
        $this->assertTrue(stream_wrapper_register($scheme, FailingExportStream::class));
        FailingExportStream::$mode = 'collision';
        FailingExportStream::$openAttempts = 0;

        try {
            try {
                $this->callMethod($this->_oModel, 'saveExportFile', [$scheme . '://export.csv', 'content']);
                $this->fail('Repeated collisions must raise an export exception.');
            } catch (ExportFileException $exception) {
                $this->assertSame(10, FailingExportStream::$openAttempts);
            }
        } finally {
            stream_wrapper_unregister($scheme);
        }
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getConnection
     * @test
     * @throws ReflectionException
     */
    public function canGetConnection()
    {
        $this->assertInstanceOf(
            Connection::class,
            $this->callMethod(
                $this->_oModel,
                'getConnection'
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
                'getRendererBridge',
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
                'getRenderer',
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
                'getRenderer',
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
                'getTitle',
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
                'getFileExtension',
            ])
            ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getExportFilenameBase')->willReturn('base');
        $modelMock->expects($this->atLeastOnce())->method('getFileExtension')->with($format)->willReturn('extension');

        $this->_oModel = $modelMock;

        $this->assertMatchesRegularExpression(
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
        /** @var Result|MockObject $resultMock */
        $resultMock = $this->getMockBuilder(Result::class)
            ->onlyMethods(get_class_methods(Result::class))
            ->getMock();
        $resultMock->method('fetchAllAssociative')->willReturn($dbResult);

        /** @var Database|MockObject $connectionMock */
        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])
            ->getMock();
        $connectionMock->expects($this->exactly((int) !$throwsException))->method('executeQuery')->willReturn($resultMock);

        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
            ->onlyMethods([
                'getConnection',
            ])
            ->getMock();
        $modelMock->expects($this->exactly((int) !$throwsException))->method('getConnection')->willReturn($connectionMock);

        $this->_oModel = $modelMock;

        try {
            $result = $this->callMethod(
                $this->_oModel,
                'getExportData',
                [[$query, ['param1', 'param2']]]
            );

            $this->assertSame(
                [
                    [
                        [
                            'field1'    => 'content1',
                            'field2'    => 'content2',
                        ],
                    ],
                    [
                        'field1',
                        'field2',
                    ],
                ],
                $result
            );
        } catch (TaskException $e) {
            if ($throwsException) {
                $this->assertStringContainsString('Export kann nicht ausgeführt werden', $e->getMessage());
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

    /**
     * @covers \D3\DataWizard\Application\Model\ExportBase::getContent
     * @test
     * @throws ReflectionException
     */
    public function canGetContent()
    {
        /** @var d3TestExport|MockObject $modelMock */
        $modelMock = $this->getMockBuilder(d3TestExport::class)
        ->onlyMethods([
            'getQuery',
            'getExportData',
            'renderContent',
        ])
        ->getMock();
        $modelMock->expects($this->atLeastOnce())->method('getQuery')->willReturn(['SELECT 1', ['arg1', 'arg2']]);
        $modelMock->expects($this->atLeastOnce())->method('getExportData')->willReturn([[1, 2], ['field1', 'field2']]);
        $modelMock->expects($this->atLeastOnce())->method('renderContent')->willReturn('some content');

        $this->_oModel = $modelMock;

        $this->assertSame(
            'some content',
            $this->callMethod(
                $this->_oModel,
                'getContent',
                ['CSV']
            )
        );
    }
}

/** Local stream wrapper used to simulate filesystem failures without touching real files. */
class FailingExportStream
{
    public $context;
    public static string $mode = '';
    public static int $openAttempts = 0;
    public static array $removedPaths = [];
    public static string $writtenContent = '';

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$openAttempts++;
        return self::$mode === 'write' || self::$mode === 'partial';
    }

    public function stream_write(string $data): int
    {
        if (self::$mode === 'partial') {
            $part = substr($data, 0, 2);
            self::$writtenContent .= $part;
            return strlen($part);
        }
        return 0;
    }

    public function stream_close(): void
    {
    }

    public function url_stat(string $path, int $flags): array|false
    {
        if (self::$mode !== 'collision') {
            return false;
        }

        return [2 => 0100644, 'mode' => 0100644];
    }

    public function unlink(string $path): bool
    {
        self::$removedPaths[] = $path;
        return true;
    }
}
