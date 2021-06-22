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

namespace D3\DataWizard\Application\Model;

interface QueryBase
{
    public function run();

    /**
     * @return string
     */
    public function getTitle() : string;

    /**
     * @return string
     */
    public function getDescription() : string;

    /**
     * @return string
     */
    public function getButtonText() : string;

    /**
     * @return array [string $query, array $parameters]
     */
    public function getQuery() : array;
}