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
use D3\DataWizard\Application\Model\Exceptions\InputUnvalidException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\ModCfg\Application\Model\d3database;
use Doctrine\DBAL\Exception as DBALException;
use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingService;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class d3ActionWizard extends AdminDetailsController
{
    protected $_sThisTemplate = '@'. Constants::OXID_MODULE_ID .'/admin/d3ActionWizard';

    protected Configuration $configuration;

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

    public function getGroupTasks($group): array
    {
        return $this->configuration->getActionsByGroup($group);
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function runTask(): void
    {
        try {
            $this->execute();
        } catch (DataWizardException|DBALException $e) {
            Registry::getLogger()->error($e->getMessage());
            Registry::getUtilsView()->addErrorToDisplay($e);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws DebugException
     * @throws InputUnvalidException
     * @throws NotFoundExceptionInterface
     * @throws TaskException
     */
    protected function execute(): void
    {
        $id = Registry::getRequest()->getRequestEscapedParameter('taskid');
        $action = $this->configuration->getActionById($id);

        [ $queryString, $parameters ] = $action->getQuery();

        if ($this->getSettingsService()->getBoolean('d3datawizard_debug', Constants::OXID_MODULE_ID)) {
            /** @var DebugException $debug */
            $debug = oxNew(
                DebugException::class,
                d3database::getInstance()->getPreparedStatementQuery($queryString, $parameters)
            );
            throw $debug;
        }

        $action->run();
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
