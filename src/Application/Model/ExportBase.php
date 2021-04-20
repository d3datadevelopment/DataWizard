<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace D3\DataWizard\Application\Model;

use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

abstract class ExportBase implements QueryBase
{
    /**
     * @param string $format
     *
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exceptions\NoSuitableRendererException
     * @throws Exceptions\TaskException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    public function run($format = RendererBridge::FORMAT_CSV)
    {
        $query = trim($this->getQuery());

        list( $rows, $fieldNames ) = $this->executeQuery( $query );

        $content = $this->renderContent($rows, $fieldNames, $format);

        /** @var $oFS d3filesystem */
        $oFS = oxNew(d3filesystem::class);
        $oFS->startDirectDownload(
            $oFS->filterFilename($this->getExportFileName($format)),
            $content
        );
    }

    /**
     * @return string
     */
    public function getButtonText() : string
    {
        return "D3_DATAWIZARD_EXPORT_SUBMIT";
    }

    /**
     * @param $format
     *
     * @return ExportRenderer\RendererInterface
     * @throws Exceptions\NoSuitableRendererException
     */
    public function getRenderer($format): ExportRenderer\RendererInterface
    {
        return oxNew(RendererBridge::class)->getRenderer($format);
    }

    /**
     * @param $format
     *
     * @return string
     * @throws Exceptions\NoSuitableRendererException
     */
    public function getFileExtension($format): string
    {
        return $this->getRenderer($format)->getFileExtension();
    }

    /**
     * @param $rows
     * @param $fieldnames
     * @param $format
     *
     * @return string
     * @throws Exceptions\NoSuitableRendererException
     */
    public function renderContent($rows, $fieldnames, $format): string
    {
        $renderer = $this->getRenderer($format);
        return $renderer->getContent($rows, $fieldnames);
    }

    /**
     * @param $format
     *
     * @return string
     * @throws Exceptions\NoSuitableRendererException
     */
    public function getExportFileName($format) : string
    {
        return $this->getExportFilenameBase().'_'.date('Y-m-d_H-i-s').'.'.$this->getFileExtension($format);
    }

    /**
     * @param string $query
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    protected function executeQuery( string $query ): array
    {
        if ( strtolower( substr( $query, 0, 6 ) ) !== 'select' ) {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString( 'D3_DATAWIZARD_ERR_NOEXPORTSELECT' )
            );
        }

        $rows = DatabaseProvider::getDb( DatabaseProvider::FETCH_MODE_ASSOC )->getAll( $query );

        if ( count( $rows ) <= 0 ) {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString( 'D3_DATAWIZARD_ERR_NOEXPORTCONTENT' )
            );
        }

        $fieldNames = array_keys( $rows[0] );

        return [ $rows, $fieldNames ];
    }
}