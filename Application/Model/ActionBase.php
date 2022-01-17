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

use D3\DataWizard\Application\Model\Exceptions\InputUnvalidException;
use FormManager\Inputs\Checkbox;
use FormManager\Inputs\Input;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

abstract class ActionBase implements QueryBase
{
    protected $formElements = [];

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function run()
    {
        if ($this->hasFormElements()) {
            /** @var Input $element */
            foreach ($this->getFormElements() as $element) {
                if (false === $element->isValid()) {
                    throw oxNew(InputUnvalidException::class, $this, $element);
                }
            }
        }

        $this->executeAction($this->getQuery());
    }

    /**
     * @param array $query
     *
     * @return int
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function executeAction(array $query): int
    {
        [ $queryString, $parameters ] = $query;

        $queryString = trim($queryString);

        if (strtolower(substr($queryString, 0, 6)) === 'select') {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString('D3_DATAWIZARD_ERR_ACTIONSELECT')
            );
        }

        $affected = $this->d3GetDb()->execute($queryString, $parameters);

        throw oxNew(
            Exceptions\TaskException::class,
            $this,
            sprintf(
                Registry::getLang()->translateString(
                    $affected === 1 ? 'D3_DATAWIZARD_ERR_ACTIONRESULT' : 'D3_DATAWIZARD_ERR_ACTIONRESULTS'
                ),
                $affected
            )
        );
    }

    /**
     * @return DatabaseInterface|null
     * @throws DatabaseConnectionException
     */
    public function d3GetDb(): ?DatabaseInterface
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * @return string
     */
    public function getButtonText(): string
    {
        return "D3_DATAWIZARD_ACTION_SUBMIT";
    }

    /**
     * @param Input $input
     */
    public function registerFormElement(Input $input)
    {
        if ($input instanceof Radio || $input instanceof Checkbox) {
            $input->setTemplate('<p class="form-check">{{ input }} {{ label }}</p>');
            $input->setAttribute('class', 'form-check-input');
        } else {
            $input->setTemplate('<p class="formElements">{{ label }} {{ input }}</p>');
            $input->setAttribute('class', 'form-control');
        }
        $this->formElements[] = $input;
    }

    /**
     * @return bool
     */
    public function hasFormElements(): bool
    {
        return (bool) count($this->formElements);
    }

    /**
     * @return array
     */
    public function getFormElements(): array
    {
        return $this->formElements;
    }
}
