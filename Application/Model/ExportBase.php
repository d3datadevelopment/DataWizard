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

use Assert\Assert;
use Assert\LazyAssertionException;
use D3\DataWizard\Application\Model\Exceptions\ExportFileException;
use D3\DataWizard\Application\Model\Exceptions\InputUnvalidException;
use D3\DataWizard\Application\Model\Exceptions\NoSuitableRendererException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\ModCfg\Application\Model\d3filesystem;
use D3\ModCfg\Application\Model\download;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use FormManager\Inputs\Checkbox;
use FormManager\Inputs\Input;
use FormManager\Inputs\Radio;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Database\ConnectionProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class ExportBase implements QueryBase
{
    protected array $formElements = [];

    public function init()
    {

    }

    /**
     * Ensure that the translations are equally available in the frontend and the backend
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * @param string $format
     * @param $path
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NoSuitableRendererException
     * @throws NotFoundExceptionInterface
     */
    public function run(string $format = RendererBridge::FORMAT_CSV, $path = null): string
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
    public function getButtonText(): string
    {
        return "D3_DATAWIZARD_EXPORT_SUBMIT";
    }

    /**
     * @throws NoSuitableRendererException
     */
    public function getRenderer(string $format): RendererInterface
    {
        return $this->getRendererBridge()->getRenderer($format);
    }

    public function getRendererBridge(): RendererBridge
    {
        return oxNew(RendererBridge::class);
    }

    /**
     * @throws NoSuitableRendererException
     */
    public function getFileExtension(string $format): string
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
    public function getExportFilenameBase(): string
    {
        return $this->getTitle();
    }

    /**
     * @param $format
     *
     * @return string
     * @throws Exceptions\NoSuitableRendererException
     */
    public function getExportFileName($format): string
    {
        return $this->getExportFilenameBase().'_'.date('Y-m-d_H-i-s').'.'.$this->getFileExtension($format);
    }

    /**
     * @param array $query
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NotFoundExceptionInterface
     */
    public function getExportData(array $query): array
    {
        [ $queryString, $parameters ] = $query;

        Assert::that($queryString)->string();
        Assert::that($parameters)->isArray();

        $queryString = trim($queryString);

        try {
            Assert::lazy()
                ->that(strtolower(trim($queryString)))
                ->startsWith(
                    'select',
                    Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTSELECT')
                )
                ->verifyNow();
        } catch (LazyAssertionException $exception) {
            throw new TaskException($this, $exception->getMessage(), $exception);
        }

        $rows = $this->getConnection()->executeQuery($queryString, $parameters)->fetchAllAssociative();

        try {
            Assert::lazy()
                ->that($rows)
                ->minCount(
                    1,
                    Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTCONTENT', null, true)
                )
                ->verifyNow();
        } catch (LazyAssertionException $exception) {
            throw new TaskException($this, $exception->getMessage(), $exception);
        }

        $fieldNames = array_keys($rows[0]);

        return [ $rows, $fieldNames ];
    }

    /**
     * @return Connection
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getConnection(): Connection
    {
        return ContainerFactory::getInstance()->getContainer()->get(ConnectionProviderInterface::class)->get();
    }

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

    /**
     * @param string $format
     * @param $path
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NoSuitableRendererException
     * @throws NotFoundExceptionInterface
     */
    protected function executeExport(string $format, $path): string
    {
        $content = $this->getContent($format);

        $oFS = $this->getFileSystem();
        if (is_null($path)) {
            $download = $this->getDownload();
            $download->startDownload($oFS->filterFilename($this->getExportFileName($format)), $content);
        } else {
            $filePath = $oFS->trailingslashit($path) . $oFS->filterFilename($this->getExportFileName($format));
            return $this->saveExportFile($filePath, $content);
        }

        return '';
    }

    /**
     * Create a file exclusively so concurrent exports cannot overwrite each other.
     */
    protected function saveExportFile(string $filePath, string $content): string
    {
        if (!stream_is_local($filePath)) {
            throw oxNew(ExportFileException::class, $filePath);
        }

        $candidate = $filePath;
        for ($attempt = 0; $attempt < 10; $attempt++) {
            $handle = @fopen($candidate, 'xb');
            if ($handle !== false) {
                $remaining = $content;
                for ($writeAttempt = 0; $remaining !== '' && $writeAttempt < strlen($content); $writeAttempt++) {
                    $bytes = @fwrite($handle, $remaining);
                    if (!$bytes) {
                        $this->closeExportFile($handle);
                        @unlink($candidate);
                        throw oxNew(ExportFileException::class, $candidate);
                    }
                    $remaining = substr($remaining, $bytes);
                }
                $closed = $this->closeExportFile($handle);
                if ($remaining !== '' || !$closed) {
                    @unlink($candidate);
                    throw oxNew(ExportFileException::class, $candidate);
                }

                $this->getFileSystem()->chmod($candidate);
                return $candidate;
            }

            clearstatcache(true, $candidate);
            if (!file_exists($candidate) && !is_link($candidate)) {
                throw oxNew(ExportFileException::class, $candidate);
            }

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $base = $extension === '' ? $filePath : substr($filePath, 0, -strlen($extension) - 1);
            $candidate = $base . '_' . bin2hex(random_bytes(8)) . ($extension === '' ? '' : '.' . $extension);
        }

        throw oxNew(ExportFileException::class, $candidate);
    }

    /** @param resource $handle */
    protected function closeExportFile($handle): bool
    {
        return fclose($handle);
    }

    protected function getDownload(): download
    {
        return oxNew(download::class);
    }
    protected function getFileSystem(): d3filesystem
    {
        return oxNew(d3filesystem::class);
    }

    /**
     * @param string $format
     * @return string
     * @throws ContainerExceptionInterface
     * @throws DBALException
     * @throws Exception
     * @throws NoSuitableRendererException
     * @throws NotFoundExceptionInterface
     */
    public function getContent(string $format): string
    {
        [ $rows, $fieldNames ] = $this->getExportData($this->getQuery());

        return $this->renderContent($rows, $fieldNames, $format);
    }

    public function getBoxTemplate(): string
    {
        $templateExtension = ContainerFactory::getInstance()->getContainer()
            ->getParameter('oxid_esales.templating.engine_template_extension');

        return "@d3datawizard/admin/inc/wizardDefaultBox.".$templateExtension;
    }
}
