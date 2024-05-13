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
use D3\DataWizard\Application\Model\Constants;
use D3\DataWizard\Application\Model\Exceptions\DataWizardException;
use D3\DataWizard\Application\Model\Exceptions\DebugException;
use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;
use D3\ModCfg\Application\Model\d3database;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3ExportWizard extends AdminDetailsController
{
    protected $_sThisTemplate = '@'. Constants::OXID_MODULE_ID .'/admin/d3ExportWizard';

    protected Configuration $configuration;

    public function __construct()
    {
        parent::__construct();

        $this->configuration = oxNew(Configuration::class);
    }

    public function getViewId(): string
    {
        return 'd3mxDataWizard_Export';
    }

    public function getGroups(): array
    {
        return $this->configuration->getExportGroups();
    }

    public function getGroupTasks($group): array
    {
        return $this->configuration->getExportsByGroup($group);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DatabaseConnectionException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    public function runTask(): void
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
     * @throws NoSuitableRendererException
     * @throws StandardException
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    protected function execute(): void
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('taskid');
        $export = $this->configuration->getExportById($id);

        [ $queryString, $parameters ] = $export->getQuery();

        if ($this->getSettingsService()->getBoolean('d3datawizard_debug', Constants::OXID_MODULE_ID)) {
            throw oxNew(
                DebugException::class,
                d3database::getInstance()->getPreparedStatementQuery($queryString, $parameters)
            );
        }

        $export->run(Registry::getRequest()->getRequestEscapedParameter('format'));
    }

    /**
     * @return ModuleSettingService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function getSettingsService(): ModuleSettingServiceInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(ModuleSettingServiceInterface::class);
    }

    public function getUserMessages(): ?string
    {
        return null;
    }

    public function getHelpURL(): ?string
    {
        return null;
    }
}
