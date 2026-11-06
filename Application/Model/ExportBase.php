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
use D3\DataWizard\Application\Model\Exceptions\RenderException;
use D3\DataWizard\Application\Model\Exceptions\TaskException;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridge;
use D3\DataWizard\Application\Model\ExportRenderer\RendererInterface;
use D3\DataWizard\Application\Model\ExportRenderer\RendererBridgeInterface;
use D3\DataWizard\Application\Model\ExportRenderer\StreamingRendererInterface;
use D3\DataWizard\Application\Model\ExportRenderer\Csv;
use D3\DataWizard\Application\Model\ExportRenderer\Json;
use D3\DataWizard\Application\Model\ExportRenderer\SpreadsheetCsv;
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

    public function hasQuery(): bool
    {
        return true;
    }

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
    public function run(string $format = Csv::FORMAT, $path = null): string
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

    public function getRendererBridge(): RendererBridgeInterface
    {
        return ContainerFactory::getInstance()->getContainer()->get(RendererBridgeInterface::class);
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
        [ $queryString, $parameters ] = $this->validateExportQuery($query);

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

    private function validateExportQuery(array $query): array
    {
        [ $queryString, $parameters ] = $query;

        Assert::that($queryString)->string();
        Assert::that($parameters)->isArray();

        $queryString = trim($queryString);

        try {
            Assert::lazy()
                ->that(strtolower($queryString))
                ->startsWith(
                    'select',
                    Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTSELECT')
                )
                ->verifyNow();
        } catch (LazyAssertionException $exception) {
            throw new TaskException($this, $exception->getMessage(), $exception);
        }

        return [$queryString, $parameters];
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
        $renderer = $this->getStreamingRenderer($format);
        if ($renderer !== null) {
            return $this->executeStreamingExport($format, $path, $renderer);
        }

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

    private function getStreamingRenderer(string $format): ?StreamingRendererInterface
    {
        // Existing extension points may provide data or alter output and must keep the legacy path.
        foreach (['getContent', 'getExportData', 'renderContent', 'getDownload', 'saveExportFile'] as $method) {
            if ((new \ReflectionMethod($this, $method))->getDeclaringClass()->getName() !== self::class) {
                return null;
            }
        }

        $renderer = $this->getRenderer($format);
        if ($renderer instanceof StreamingRendererInterface) {
            return $renderer;
        }

        return null;
    }

    private function executeStreamingExport(string $format, $path, StreamingRendererInterface $renderer): string
    {
        $stream = $this->createExportStream();
        if ($stream === false) {
            throw oxNew(RenderException::class, 'Could not create temporary export file', []);
        }

        try {
            [$queryString, $parameters] = $this->validateExportQuery($this->getQuery());
            $result = $this->getConnection()->executeQuery($queryString, $parameters);
            try {
                $first = $result->fetchAssociative();
                if ($first === false) {
                    throw new TaskException(
                        $this,
                        Registry::getLang()->translateString('D3_DATAWIZARD_ERR_NOEXPORTCONTENT', null, true)
                    );
                }
                $rows = (static function () use ($first, $result): \Generator {
                    yield $first;
                    while (($row = $result->fetchAssociative()) !== false) {
                        yield $row;
                    }
                })();
                $renderer->writeContent($stream, $rows, array_keys($first));
            } finally {
                $result->free();
            }

            if (!$this->rewindExportStream($stream)) {
                throw oxNew(RenderException::class, 'Could not rewind export stream', []);
            }

            $fs = $this->getFileSystem();
            $filename = $fs->filterFilename($this->getExportFileName($format));
            if ($path === null) {
                $this->downloadStream($filename, $stream);
                return '';
            }

            return $this->saveExportStream($fs->trailingslashit($path) . $filename, $stream);
        } finally {
            fclose($stream);
        }
    }

    /** @return resource|false */
    protected function createExportStream()
    {
        return tmpfile();
    }

    /** @param resource $stream */
    protected function rewindExportStream($stream): bool
    {
        return rewind($stream);
    }

    /** @param resource $stream */
    protected function downloadStream(string $filename, $stream): void
    {
        oxNew(StreamDownload::class)->startStreamDownload($filename, $stream);
    }

    /** @param resource $stream */
    protected function saveExportStream(string $filePath, $stream): string
    {
        return $this->publishExport($filePath, static function ($destination) use ($stream): bool {
            $stat = fstat($stream);
            return $stat !== false && stream_copy_to_stream($stream, $destination) === $stat['size'];
        });
    }

    /**
     * Create a file exclusively so concurrent exports cannot overwrite each other.
     */
    protected function saveExportFile(string $filePath, string $content): string
    {
        return $this->publishExport($filePath, static function ($destination) use ($content): bool {
            $offset = 0;
            $length = strlen($content);
            for ($attempt = 0; $offset < $length && $attempt < $length; $attempt++) {
                $written = @fwrite($destination, substr($content, $offset));
                if (!$written) {
                    return false;
                }
                $offset += $written;
            }
            return $offset === $length;
        });
    }

    /** Publish a complete local file without replacing an existing destination. */
    private function publishExport(string $filePath, callable $write): string
    {
        if (!stream_is_local($filePath) || preg_match('~^[a-z][a-z0-9+.-]*://~i', $filePath)) {
            throw oxNew(ExportFileException::class, $filePath);
        }
        $directory = dirname($filePath);
        $temporary = $directory . DIRECTORY_SEPARATOR . '.d3export_' . bin2hex(random_bytes(16)) . '.tmp';
        $handle = @fopen($temporary, 'xb');
        if ($handle === false) {
            throw oxNew(ExportFileException::class, $filePath);
        }
        try {
            $written = $write($handle);
            $closed = $this->closeExportFile($handle);
            $handle = null;
            if (!$written || !$closed) {
                throw oxNew(ExportFileException::class, $filePath);
            }
            $this->getFileSystem()->chmod($temporary);
            $candidate = $filePath;
            foreach (range(1, 10) as $attempt) {
                if (@link($temporary, $candidate)) {
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
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
            @unlink($temporary);
        }
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
