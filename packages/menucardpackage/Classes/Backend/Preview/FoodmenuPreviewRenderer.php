<?php

declare(strict_types=1);

namespace menucardvendor\menucardpackage\Backend\Preview;

use TYPO3\CMS\Backend\Preview\PreviewRendererInterface;
use TYPO3\CMS\Backend\View\BackendLayout\Grid\GridColumnItem;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\View\ViewFactoryData;
use TYPO3\CMS\Core\View\ViewFactoryInterface;

class FoodmenuPreviewRenderer implements PreviewRendererInterface
{
    public function renderPageModulePreviewHeader(GridColumnItem $item): string
    {
        $contentElements = $item->getColumn()->getItems();
        $view = new ViewFactoryData(templateRootPaths: ['EXT:menucardpackage/Resources/Private/Templates/Preview']);
        $viewFactory = GeneralUtility::makeInstance(ViewFactoryInterface::class);
        $view = $viewFactory->create($view);
        
        foreach ($contentElements as $element)
        {
            $record = $element->getRecord();
            if (isset($record['CType']) && $record['CType'] === 'foodmenu' && isset($record['menu_cols']) && $record['menu_cols'] > 0)
            {
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_foodmenu_columns');
                
                $columns = $queryBuilder
                    ->select('*')
                    ->from('tx_foodmenu_columns')
                    ->where(
                        $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($record['uid'], Connection::PARAM_INT))
                    )
                    ->executeQuery()
                    ->fetchAllAssociative();
                
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_foodmenu_dishes');

                foreach ($columns as &$column)
                {
                    $dishes = $queryBuilder
                        ->select('*')
                        ->from('tx_foodmenu_dishes')
                        ->where(
                            $queryBuilder->expr()->eq('parentid', $queryBuilder->createNamedParameter($column['uid'], Connection::PARAM_INT))
                        )
                        ->executeQuery()
                        ->fetchAllAssociative();
                    $column['dishes'] = $dishes;
                }
                $view->assign('columns', $columns);

            }
        }

        //$view->render();
        return '';
       
        //https://topwire.dev/
        //https://github.com/ErHaWeb/klaro_consent_manager/blob/main/Classes/Service/KlaroService.php#L281-L30 
    }

    public function renderPageModulePreviewContent(GridColumnItem $item): string
    {
        dd($item);
        return '';
    }

    public function renderPageModulePreviewFooter(GridColumnItem $item): string
    {
        return '';
    }

    public function wrapPageModulePreview(string $previewHeader, string $previewContent, GridColumnItem $item): string
    {
        return $previewHeader;
    }
}

?>