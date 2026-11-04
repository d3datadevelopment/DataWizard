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

use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use Exception;
use OxidEsales\Eshop\Core\Registry;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

class ExportFileExceptionTest extends d3ModCfgUnitTestCase
{
    /** @var ExportFileException */
    protected $_oModel;

    /**
     * @covers \D3\DataWizard\Application\Model\Exceptions\ExportFileException::__construct
     * @test
     * @throws ReflectionException
     */
    public function canConstruct()
    {
        $code = '0';

        $exception = oxNew(Exception::class);

        $this->_oModel = oxNew(ExportFileException::class, 'testMessage', $exception);

        $this->assertStringContainsString(
            'kann nicht angelegt werden',
            $this->callMethod(
                $this->_oModel,
                'getMessage'
            )
        );

        $this->assertEquals(
            $code,
            $this->callMethod(
                $this->_oModel,
                'getCode'
            )
        );

        $this->assertSame(
            [$exception],
            $this->callMethod(
                $this->_oModel,
                'getErrorExceptions'
            )
        );
    }
}
