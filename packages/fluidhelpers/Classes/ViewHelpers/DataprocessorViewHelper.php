<?php

/**
 * Author: Dennis Schwab - 2025
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

declare(strict_types=1);

namespace DS\fluidHelpers\ViewHelpers;

use DS\fluidHelpers\Utility\SimpleDatabaseQuery;
use DS\fluidHelpers\Utility\SimpleDatabaseQueryException;
use DS\fluidHelpers\Utility\Utility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use DS\fluidHelpers\ViewHelpers\SetViewHelper;
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Resource\ResourceFactory;

class DataprocessorViewHelperException extends Exception {}

/**
 * Provide data to FLUID from either the content object or the database.
 * Usage:
 * 
 * Include the Fluid-view-helpers:
 * 
 * < html
 *  xmlns:be="http://typo3.org/ns/TYPO3/CMS/Backend/ViewHelpers"
 *  xmlns:fh="http://typo3.org/ns/DS/fluidHelpers/ViewHelpers"
 *  data-namespace-typo3-fluid="true" >
 * 
 * For database queries:
 * 
 * <fh:dataprocessor source="db" nested="true" where="uid > $uid && (pid == 20 || pid == $pageId)" variables="pageId: 5, uid: 60" order="uid DESC, pid DESC" as="processedData" />
 * 
 * or
 * 
 * The content object:
 * <fh:dataprocessor source="page" nested="false" as="processedData" />
 * 
 */
use TYPO3\CMS\Core\Resource\Exception\FileDoesNotExistException;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class DataprocessorViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    protected $foreignTables = [];
    protected StorageRepository $storageRepository;

    public function initializeArguments(): void
    {
        $this->registerArgument (
            'source',
            'string',
            'Can either be "db" (fetch the data from the database) or "page" (get data from the content object).',
            false,
            'page'
        );
        $this->registerArgument('nested', 'bool', 'If true, the data processor will fetch data from the db whereas an item has ' .
        'foreign_table set in the TCA-configuration.', false, false);
        $this->registerArgument('tableName', 'string', 'The table identifier to fetch the data from', false, 'tt_content');
        $this->registerArgument('select', 'string', 'The select filter to fetch only specific columns. Comma-separated', false, '*');
        $this->registerArgument (
            'where',
            'string',
            'A where filter expression in format: fieldName COMPAREOPERAND value. Like: pid == 5 && CType != "textpic".' .
            'Valid expression operators are: &&, ||, ==, !=, <=, >=, <, >, %%',
            false,
            'uid == 0'
        );
        $this->registerArgument (
            'variables',
            'mixed',
            'Define comma seperated key: value pairs to replace placeholders in the where clause. E.g.: myKey: myValue, myKey2: myValue2, ...',
            false,
            ''
        );
        $this->registerArgument (
            'order',
            'string',
            'Define comma seperated order rules. E.g.: columnName1 ASC, columnName2 DESC',
            false,
            ''
        );
        $this->registerArgument (
            'useNative',
            'bool',
            'If you wish to do more complex query strings, you can pass DQL by setting this to true.',
            false,
            false
        );
        $this->registerArgument (
            'as',
            'string',
            'Defines the key to access the results in FLUID.',
            true
        );
    }
    
    private function _varStringToArray(): array
    {
        return Utility::keyValueStringToArray($this->arguments['variables']);
    }

    private function _getTtContent(): array
    {
        $request = $this->_getRequest();
        $cObj = $request->getAttribute('currentContentObject');
        if ($cObj)
        {
            return $cObj->data;
        }
        else
        {
            $globalVars = $this->renderingContext->getVariableProvider();
            if ($globalVars->exists('uid'))
            {
                return $globalVars->getAll();
            }
            else throw new DataprocessorViewHelperException("Relevant page information can't get gathered.");
        }
    }

    private function _filterStdEntries(): array
    {
        $stdKeys =
        [
            'uid', 'pid', 'tstamp', 'crdate', 'deleted', 'hidden', 'starttime', 'endtime',
            'fe_group', 'sorting', 'rowDescription', 'editlock', 'sys_language_uid', 'l18n_parent',
            'l10n_source', 'l10n_state', 'l18n_diffsource', 't3ver_oid', 't3ver_wsid', 't3ver_state',
            't3ver_stage', 'frame_class', 'colPos', 'table_caption', 'tx_impexp_origuid', 'CType',
            'categories', 'layout', 'space_before_class', 'space_after_class', 'date', 'header',
            'header_layout', 'header_position', 'header_link', 'subheader', 'bodytext', 'image',
            'assets', 'imagewidth', 'imageheight', 'imageorient', 'imageborder', 'image_zoom',
            'imagecols', 'pages', 'recursive', 'list_type', 'media', 'records', 'sectionIndex',
            'linkToTop', 'pi_flexform', 'selected_categories', 'category_field', 'bullets_type',
            'cols', 'table_class', 'table_delimiter', 'table_enclosure', 'table_header_position',
            'table_tfoot', 'file_collections', 'filelink_size', 'filelink_sorting', 'target',
            'uploads_description', 'filelink_sorting_direction', 'uploads_type'

        ];
        return array_filter($GLOBALS['TCA']['tt_content']['columns'], function($key) use ($stdKeys) {
            return array_search($key, $stdKeys) === false;
        }, ARRAY_FILTER_USE_KEY);
    }

    private function _fetchChildTable(array &$array, array $tca, string $parentTableName, int $parentId): void
    {
        $sdq = new SimpleDatabaseQuery();
        if ($parentTableName === 'sys_file_reference' && array_key_exists('uid_local', $array)) {
            $resourceFactory = GeneralUtility::makeInstance(ResourceFactory::class);
            $array['file'] = $resourceFactory->getFileObject($array['uid_local']);
        } else {
            foreach ($tca as $key => $entry)
            {
                if ($key !== 'l10n_parent' && array_key_exists('config', $entry))
                {
                    $config = $entry['config'];
                    if (array_key_exists('foreign_table', $config))
                    {
                        $foreignTable = $config['foreign_table'];
                        if ($sdq->tableExists($foreignTable))
                        {
                            $foreignField = array_key_exists('foreign_field', $config) ? $config['foreign_field'] : '';
                            $foreignTableField = array_key_exists('foreign_table_field', $config) ? $config['foreign_table_field'] : '';
                            $where = '';
                            if ($foreignTableField !== '') $where .= $foreignTableField . "=='$parentTableName'";
                            if ($where !== '') $where .= '&&';
                            if ($foreignField !== '') $where .= $foreignField . "==$parentId";
                            if ($where !== '')
                            {
                                $results = $sdq->fetch (
                                    $foreignTable,
                                    '*',
                                    $where
                                );
                            }
                            if (is_array($results) && !empty($results))
                            {
                                if (array_key_exists($foreignTable, $GLOBALS['TCA']))
                                {
                                    
                                    foreach ($results as &$result)
                                    {
                                        if (array_key_exists('uid', $result))
                                        {
                                            $uid = $result['uid'];
                                            $this->_fetchChildTable($result, $GLOBALS['TCA'][$foreignTable]['columns'], $foreignTable, $uid);
                                        }
                                    }
                                }
                                $array[$key] = $results;

                                //
                            }
                        }
                    }
                } 
            }
        }
    }

    private function _fetchChildTables(&$data, $tableName): void
    {
        if (array_key_exists('uid', $data) && ($data['uid'] || $data['uid'] != ''))
        {
            $customEntries = $this->_filterStdEntries();
            $results = [];
            $this->_fetchChildTable($results, $customEntries, $tableName, $data['uid']);
            $data = array_merge($data, $results);
        }
    }
    

    private function _getRequest(): ServerRequestInterface|null
    {
        if ($this->renderingContext->hasAttribute(ServerRequestInterface::class)) {
            return $this->renderingContext->getAttribute(ServerRequestInterface::class);
        }
        return null;
    }

    public function render(): void
    {   
        $tableName = $this->arguments['tableName'] ?? 'tt_content';
        $variables = $this->arguments['variables'];
        $data = [];

        if (!is_array($variables))
        {
            if (!is_string($variables))
            {
                throw new SimpleDatabaseQueryException (
                    "The argument 'variables' has to be declared as an array or a string in Fluid. " .
                    "E.g.: {myKey1: 'myValue1', myKey2: 2, ...} OR myKey1: 'myValue1', myKey2: 2, ..."
                );
            }
            else $variables = $this->_varStringToArray();
        }
        
        if (strtolower($this->arguments['source']) === 'page')
        {
            $data = $this->_getTtContent();
        }
        else if (strtolower($this->arguments['source']) === 'db')
        {
            $sdq = new SimpleDatabaseQuery();
            $results = $sdq->fetch (
                $tableName,
                $this->arguments['select'],
                $this->arguments['where'],
                $this->arguments['order'],
                $variables,
                $this->arguments['useNative']
            );
            $data = $results;
        }
        
        if ($this->arguments['nested'] == true && !empty($data))
        {
            if (strtolower($this->arguments['source']) === 'page')
            {
                $this->_fetchChildTables($data, $tableName);
            }
            else if (strtolower($this->arguments['source']) === 'db')
            {
                $index = 0;
                foreach ($data as $entry)
                {
                    $this->_fetchChildTables($entry, $tableName);
                    $data[$index++] = $entry;
                }
            }
        }
        $globalVars = $this->renderingContext->getVariableProvider();
        $globalVars->add($this->arguments['as'], $data);
    }
}