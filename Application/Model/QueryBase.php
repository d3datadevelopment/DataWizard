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

use FormManager\Inputs\Input;

interface QueryBase
{
    public function run();

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getTitle(): string;

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return string
     */
    public function getButtonText(): string;

    /**
     * @return array [string $query, array $parameters]
     */
    public function getQuery(): array;

    /**
     * @param Input $input
     */
    public function registerFormElement(Input $input);

    /**
     * @return bool
     */
    public function hasFormElements(): bool;

    /**
     * @return array
     */
    public function getFormElements(): array;
}
