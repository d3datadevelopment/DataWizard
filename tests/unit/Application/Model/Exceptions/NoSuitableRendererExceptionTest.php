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

use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use Exception;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Language;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;

/**
 * @coversNothing
 */
class NoSuitableRendererExceptionTest extends d3ModCfgUnitTestCase
{
    public function testUsesAdminTranslationsOutsideAdminContext(): void
    {
        $originalLanguage = Registry::getLang();
        $language = $this->createMock(Language::class);
        $language->expects($this->once())
            ->method('translateString')
            ->with('D3_DATAWIZARD_ERR_NOSUITABLERENDERER', null, true)
            ->willReturn('The export format "%1$s" is not available.');
        Registry::set(Language::class, $language);

        try {
            $exception = new NoSuitableRendererException('xyz');
            $this->assertSame('The export format "xyz" is not available.', $exception->getMessage());
        } finally {
            Registry::set(Language::class, $originalLanguage);
        }
    }

    /** @var NoSuitableRendererException */
    protected $_oModel;

    /**
     * @covers \D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException::__construct
     * @test
     * @throws ReflectionException
     */
    public function canConstruct()
    {
        $code = '0';

        $exception = oxNew(Exception::class);

        $this->_oModel = oxNew(NoSuitableRendererException::class, 'testMessage', $exception);

        $this->assertStringContainsString(
            'Das Exportformat',
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
