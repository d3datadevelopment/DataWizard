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

namespace D3\DataWizard\tests\unit\Application\Model\ExportRenderer;

use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\ModCfg\Tests\unit\d3ModCfgUnitTestCase;
use ReflectionException;

abstract class ExportRendererTest extends d3ModCfgUnitTestCase
{
    /** @var RendererInterface */
    protected $_oModel;

    public function tearDown() : void
    {
        parent::tearDown();

        unset($this->_oModel);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getFileExtension
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::getFileExtension
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Pretty::getFileExtension
     * @test
     * @throws ReflectionException
     */
    public function canGetFileExtension()
    {
        $this->assertRegExp(
            "/^[a-z0-9._-]*$/i",
            $this->callMethod(
                $this->_oModel,
                'getFileExtension'
            )
        );
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Csv::getTitleTranslationId
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::getTitleTranslationId
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Pretty::getTitleTranslationId
     * @test
     * @throws ReflectionException
     */
    public function canGetTitleTranslationId()
    {
        $this->assertIsString(
            $this->callMethod(
                $this->_oModel,
                'getTitleTranslationId'
            )
        );
    }
}