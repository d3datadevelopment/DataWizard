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

use D3\DataWizard\Application\Model\Exceptions\RenderException;
use D3\DataWizard\Application\Model\ExportRenderer\Json;

class JsonTest extends ExportRendererTest
{
    /** @var Json */
    protected $_oModel;

    public function setUp() : void
    {
        parent::setUp();

        $this->_oModel = oxNew(Json::class);
    }

    /**
     * @covers \D3\DataWizard\Application\Model\ExportRenderer\Json::getContent
     * @test
     * @throws \ReflectionException
     * @dataProvider canGetContentDataProvider
     */
    public function canGetContent($valueList, $expectException)
    {
        $fieldList = ['field1', 'field2'];

        if ($expectException) {
            $this->expectException(RenderException::class);
        }

        $this->assertJson(
            $this->callMethod(
                $this->_oModel,
                'getContent',
                [$valueList, $fieldList]
            )
        );
    }

    /**
     * @return \string[][]
     */
    public function canGetContentDataProvider(): array
    {
        return [
            'valid' => [['value1', 'value2'], false],
            'invalid'   => [["text" => "\xB1\x31"], true]  // malformed UTF8 chars
        ];
    }
}