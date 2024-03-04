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
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use Doctrine\DBAL\Exception as DBALException;
use FormManager\Inputs\Checkbox;
use FormManager\Inputs\Input;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class ActionBase implements QueryBase
{
    protected array $formElements = [];

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws InputUnvalidException
     * @throws NotFoundExceptionInterface
     * @throws TaskException
     */
    public function run(): void
    {
        if ($this->hasFormElements()) {
            /** @var Input $element */
            foreach ($this->getFormElements() as $element) {
                if (false === $element->isValid()) {
                    /** @var InputUnvalidException $exception */
                    $exception = oxNew(InputUnvalidException::class, $this, $element);
                    throw $exception;
                }
            }
        }

        $this->executeAction($this->getQuery());
    }

    /**
     * @param array $query
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws TaskException
     * @throws DBALException
     */
    public function executeAction(array $query): void
    {
        [ $queryString, $parameters ] = $query;

        $queryString = trim($queryString);

        if (strtolower(substr($queryString, 0, 6)) === 'select') {
            /** @var TaskException $exception */
            $exception = oxNew(
                TaskException::class,
                $this,
                Registry::getLang()->translateString('D3_DATAWIZARD_ERR_ACTIONSELECT')
            );
            throw $exception;
        }

        $connection = ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get();
        $affected = (int) $connection->executeStatement($queryString, $parameters);

        /** @var TaskException $exception */
        $exception = oxNew(
            TaskException::class,
            $this,
            sprintf(
                Registry::getLang()->translateString(
                    $affected === 1 ? 'D3_DATAWIZARD_ERR_ACTIONRESULT' : 'D3_DATAWIZARD_ERR_ACTIONRESULTS'
                ),
                $affected
            )
        );
        throw $exception;
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
    public function registerFormElement(Input $input): void
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
