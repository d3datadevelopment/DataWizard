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

namespace D3\DataWizard\Application\Controller\Admin;

use D3\DataWizard\Application\Model\Configuration;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\ModCfg\Application\Model\d3database;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Registry;

class d3ActionWizard extends AdminDetailsController
{
    protected $_sThisTemplate = 'd3ActionWizard.tpl';

    /** @var Configuration */
    protected $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = oxNew(Configuration::class);
    }

    public function getViewId()
    {
        return 'd3mxDataWizard_Action';
    }
    public function getGroups(): array
    {
        return $this->configuration->getActionGroups();
    }

    public function getGroupTasks($group)
    {
        return $this->configuration->getActionsByGroup($group);
    }

    /**
     * @throws DatabaseConnectionException
     */
    public function runTask()
    {
        try {
            $this->execute();
        } catch (DataWizardException|DBALException|DatabaseErrorException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }

    /**
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function execute()
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('taskid');
        $action = $this->configuration->getActionById($id);

        [ $queryString, $parameters ] = $action->getQuery();

        if ($this->d3GetConfig()->getConfigParam('d3datawizard_debug')) {
            throw oxNew(
                DebugException::class,
                d3database::getInstance()->getPreparedStatementQuery($queryString, $parameters)
            );
        }

        $action->run();
    }

    /**
     * @return Config
     */
    public function d3GetConfig(): Config
    {
        return Registry::getConfig();
    }

    public function getUserMessages()
    {
        return null;
    }

    public function getHelpURL()
    {
        return null;
    }
}
