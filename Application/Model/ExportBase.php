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

use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\DataWizard\Application\Model\Exceptions\InputUnvalidException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Application\Model\Exception\d3_cfg_mod_exception;
use D3\ModCfg\Application\Model\Exception\d3ShopCompatibilityAdapterException;
use Doctrine\DBAL\DBALException;
use FormManager\Inputs\Checkbox;
use FormManager\Inputs\Input;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Exception\DatabaseConnectionException;
use OxidEsales\Eshop\Core\Exception\DatabaseErrorException;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

abstract class ExportBase implements QueryBase
{
    protected $formElements = [];

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getDescription() : string
    {
        return '';
    }
    
    /**
     * @param string $format
     * @param $path
     *
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exceptions\NoSuitableRendererException
     * @throws Exceptions\TaskException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     * @return string
     */
    public function run( string $format = RendererBridge::FORMAT_CSV, $path = null): string
    {
        if ($this->hasFormElements()) {
            /** @var Input $element */
            foreach ($this->getFormElements() as $element) {
                if (false === $element->isValid()) {
                    throw oxNew(InputUnvalidException::class, $this, $element);
                }
            }
        }

        return $this->executeExport($format, $path);
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
        return $this->getRendererBridge()->getRenderer($format);
    }

    /**
     * @return RendererBridge
     */
    public function getRendererBridge()
    {
        return oxNew(RendererBridge::class);
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
     * @return string
     */
    public function getExportFilenameBase() : string
    {
        return $this->getTitle();
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
     * @param array $query
     *
     * @return array
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     */
    public function getExportData( array $query ): array
    {
        [ $queryString, $parameters ] = $query;

        $queryString = trim($queryString);

        if ( strtolower( substr( $queryString, 0, 6 ) ) !== 'select' ) {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString( 'D3_DATAWIZARD_ERR_NOEXPORTSELECT' )
            );
        }

        $rows = $this->d3GetDb()->getAll( $queryString, $parameters );

        if ( count( $rows ) <= 0 ) {
            throw oxNew(
                Exceptions\TaskException::class,
                $this,
                Registry::getLang()->translateString( 'D3_DATAWIZARD_ERR_NOEXPORTCONTENT', null, true )
            );
        }

        $fieldNames = array_keys( $rows[0] );

        return [ $rows, $fieldNames ];
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

    /**
     * @param string $format
     * @param $path
     * @return string
     * @throws DBALException
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exceptions\NoSuitableRendererException
     * @throws StandardException
     * @throws d3ShopCompatibilityAdapterException
     * @throws d3_cfg_mod_exception
     */
    protected function executeExport(string $format, $path): string
    {
        $content = $this->getContent( $format );

        /** @var $oFS d3filesystem */
        $oFS = $this->getFileSystem();
        if (is_null($path)) {
            $oFS->startDirectDownload($oFS->filterFilename($this->getExportFileName($format)), $content);
        } else {
            $filePath = $oFS->trailingslashit($path) . $oFS->filterFilename($this->getExportFileName($format));
            if (false === $oFS->createFile($filePath, $content, true)) {
                throw oxNew(ExportFileException::class, $filePath);
            }
            return $filePath;
        }

        return '';
    }

    /**
     * @return \OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface|null
     * @throws DatabaseConnectionException
     */
    protected function d3GetDb(): ?\OxidEsales\Eshop\Core\Database\Adapter\DatabaseInterface
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

    /**
     * @return d3filesystem|mixed
     */
    protected function getFileSystem()
    {
        return oxNew(d3filesystem::class);
    }

    /**
     * @param string $format
     *
     * @return string
     * @throws DatabaseConnectionException
     * @throws DatabaseErrorException
     * @throws Exceptions\NoSuitableRendererException
     */
    public function getContent( string $format ): string
    {
        [ $rows, $fieldNames ] = $this->getExportData( $this->getQuery() );

        $content = $this->renderContent( $rows, $fieldNames, $format );

        return $content;
    }
}