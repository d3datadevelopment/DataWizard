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
use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\ModCfg\Application\Model\d3database;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class d3ExportWizard extends AdminDetailsController
{
    protected $_sThisTemplate = 'd3ExportWizard.tpl';

    /** @var Configuration */
    protected $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = oxNew(Configuration::class);
    }

    public function getGroups(): array
    {
        return $this->configuration->getExportGroups();
    }

    public function getGroupTasks($group)
    {
        return $this->configuration->getExportsByGroup($group);
    }

    /**
     * @throws DatabaseConnectionException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
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
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws StandardException
     * @throws NoSuitableRendererException
     * @throws TaskException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    protected function execute()
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('taskid');
        $export = $this->configuration->getExportById($id);

        [ $queryString, $parameters ] = $export->getQuery();

        if ($this->d3GetConfig()->getConfigParam('d3datawizard_debug')) {
            throw oxNew(
                DebugException::class,
                d3database::getInstance()->getPreparedStatementQuery($queryString, $parameters)
            );
        }

        $export->run(Registry::getRequest()->getRequestEscapedParameter('format'));
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