<?php

namespace SoliDry\Controllers;

use SoliDry\Exceptions\DirectoryException;
use SoliDry\Types\ConsoleInterface;
use SoliDry\Types\CustomsInterface;
use SoliDry\Types\DirsInterface;
use SoliDry\Types\ErrorsInterface;
use SoliDry\Types\ApiInterface;
use SoliDry\Types\PhpInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Trait HistoryTrait
 *
 * @package SoliDry\Controllers
 */
trait HistoryTrait
{
    /**
     *  Collects all attrs, Types and diffs for further code-generation
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function setMergedTypes(): void
    {
        $opMerge = $this->options[ConsoleInterface::OPTION_MERGE];
        $timeCheck = strtotime($opMerge); // only for validation - with respect to diff timezones

        if (false !== $timeCheck) {
            try {
                $this->mergeTime($opMerge);
            } catch (DirectoryException $e) {
                $this->error($e->getTraceAsString());
            }
        } else if (is_numeric($opMerge) !== false) {
            $this->mergeStep($opMerge);
        } else if ($opMerge === ConsoleInterface::MERGE_DEFAULT_VALUE) {
            $this->mergeLast();
        }
    }

    /**
     * Merges history OAS files with current by time in the past
     *
     * @param string $opMerge
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     * @throws DirectoryException
     */
    private function mergeTime(string $opMerge): void
    {
        $dateTime = explode(PhpInterface::SPACE, $opMerge);
        $this->composeTypes($this->composeTimeFiles($dateTime), $this->files);
    }

    /**
     * @param array $dateTime
     * @return array
     * @throws DirectoryException
     */
    private function composeTimeFiles(array $dateTime): array
    {
        $time = str_replace(':', '', $dateTime[1]);

        $this->genDir = $dateTime[0];
        $path = DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $this->genDir . DIRECTORY_SEPARATOR;

        if (is_dir($path) === false) {
            throw new DirectoryException('The directory: ' . $path . ' was not found.',
                ErrorsInterface::CODE_DIR_NOT_FOUND);
        }

        $files = glob($path . $time . '*');
        foreach ($files as &$fullPath) {
            $fullPath = str_replace($path, '', $fullPath);
        }

        $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);

        return $this->adjustFiles($files);
    }

    /**
     * Merges history OAS files with current by backward steps
     *
     * @param int $step
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function mergeStep(int $step): void
    {
        $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
        if ($dirs !== false) {
            $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
            $this->composeTypes($this->composeStepFiles($dirs, $step), $this->files);
        }
    }

    /**
     * Composes files for step back in history via .gen dir
     *
     * @param array $dirs
     * @param int $step
     * @return array
     */
    private function composeStepFiles(array $dirs, int $step): array
    {
        $filesToPass = [];
        foreach ($dirs as $dir) {
            $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $dir, SCANDIR_SORT_DESCENDING);
            $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);

            $prefixFlag = '';
            foreach ($files as $kFile => $file) {
                $prefix = substr($file, 0, 6); // Hms
                $template = '/^' . $prefix . '.*$/i';

                if ($prefix !== $prefixFlag) {
                    --$step;
                    $prefixFlag = $prefix;
                    if ($step > 0) {
                        $skip = preg_grep($template, $files);
                        $files = array_diff($files, $skip);
                    }
                }

                if ($step <= 0) {
                    $files = preg_grep($template, $files);
                    $this->genDir = $dir;
                    $filesToPass = $files;
                    break 2;
                }
            }
        }

        return $this->adjustFiles($filesToPass);
    }

    /**
     *  Merges current state with the last one
     *
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function mergeLast(): void
    {
        $lastFiles = $this->getLastFiles();
        if (empty($lastFiles) === false) {
            $this->composeTypes($lastFiles, $this->files);
        }
    }

    /**
     * Gets last files according to main file named "openapi" by spec of OAS
     * and it's included files defined in "uses" property
     *
     * @return array
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function getLastFiles(): array
    {
        $dirs = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR, SCANDIR_SORT_DESCENDING);
        if ($dirs !== false) {
            $dirs = array_diff($dirs, DirsInterface::EXCLUDED_DIRS);
            $this->genDir = $dirs[0]; // desc last date YYYY-mm-dd

            $files = scandir(DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $this->genDir, SCANDIR_SORT_DESCENDING);
            $files = array_diff($files, DirsInterface::EXCLUDED_DIRS);

            $lastFiles = [];
            foreach ($files as $file) {
                if (($pos = strpos($file, ApiInterface::OPEN_API_KEY)) !== false) {
                    $lastFiles[] = $file;
                    $content = Yaml::parse(file_get_contents($this->formatGenPathByDir() . $file));
                    if (empty($content[ApiInterface::RAML_KEY_USES]) === false) {
                        foreach ($content[ApiInterface::RAML_KEY_USES] as $subFile) {
                            $lastFiles[] = substr($file, 0, $pos) . basename($subFile);
                        }
                    }
                    break;
                }
            }

            return $this->adjustFiles($lastFiles);
        }

        return [];
    }

    /**
     * Gets history files and merges them with current OAS files
     *
     * @param array $files      files from .gen/ dir saved history
     * @param array $inputFiles file that were passed as an option + files from uses RAML property
     * @throws \Symfony\Component\Yaml\Exception\ParseException
     */
    private function composeTypes(array $files, array $inputFiles): void
    {
        $attrsCurrent = [];
        $attrsHistory = [];

        $path = DirsInterface::GEN_DIR . DIRECTORY_SEPARATOR . $this->genDir . DIRECTORY_SEPARATOR;
        foreach ($files as $file) {
            foreach ($inputFiles as $inFile) {

                if (mb_strpos($file, basename($inFile), null, PhpInterface::ENCODING_UTF8) !== false) {
                    $dataCurrent = Yaml::parse(file_get_contents($inFile));
                    $dataHistory = Yaml::parse(file_get_contents($path . $file));

                    $this->currentTypes = $dataCurrent[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
                    $this->historyTypes = $dataHistory[ApiInterface::API_COMPONENTS][ApiInterface::API_SCHEMAS];
                    $this->types += array_merge_recursive($this->historyTypes, $this->currentTypes);

                    $attrsCurrent += array_filter($this->currentTypes, function ($k) {
                        return strpos($k, CustomsInterface::CUSTOM_TYPES_ATTRIBUTES) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                    $attrsHistory += array_filter($this->historyTypes, function ($k) {
                        return strpos($k, CustomsInterface::CUSTOM_TYPES_ATTRIBUTES) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                }
            }
        }

        $this->composeDiffs($attrsCurrent, $attrsHistory);
    }

    /**
     * Compares attributes for current and previous history and sets the diffTypes prop
     * to process additional migrations creation
     *
     * @param array $attrsCurrent Current attributes
     * @param array $attrsHistory History attributes
     */
    private function composeDiffs(array $attrsCurrent, array $attrsHistory): void
    {
        // make diffs on current array to add columns/indices to migrations
        foreach ($attrsCurrent as $k => $v) {
            if (empty($attrsHistory[$k][ApiInterface::RAML_PROPS]) === false
                && (empty($v[ApiInterface::RAML_PROPS]) === false)) {

                foreach ($v[ApiInterface::RAML_PROPS] as $attr => $attrValue) {
                    if (empty($attrsHistory[$k][ApiInterface::RAML_PROPS][$attr])) { // if there is no such element in history data - collect
                        $this->diffTypes[$k][$attr] = $attrValue;
                    }
                }
            }
        }

        // reflect array from history to append lost props
        foreach ($attrsHistory as $k => $v) {
            if (empty($attrsCurrent[$k][ApiInterface::RAML_PROPS]) === false
                && (empty($v[ApiInterface::RAML_PROPS]) === false)) {

                foreach ($v[ApiInterface::RAML_PROPS] as $attr => $attrValue) {
                    if (empty($attrsCurrent[$k][ApiInterface::RAML_PROPS][$attr])) { // if there is no such element in current data - collect
                        $this->diffTypes[$k][$attr] = $attrValue;
                    }
                }
            }
        }
    }

    /**
     * Gets an unordered array of files and returns an ordered one
     * stating from *openapi.yaml
     *
     * @param array $files
     * @return array
     */
    private function adjustFiles(array $files): array
    {
        $tmpFile = '';
        foreach ($files as $k => $file) {
            if (strpos($file, ApiInterface::OPEN_API_KEY) !== false) {
                $tmpFile = $file;
                unset($files[$k]);
                break;
            }
        }
        array_unshift($files, $tmpFile);

        return $files;
    }
}