<?php

namespace DS\fluidHelpers\ViewHelpers;

use DS\fluidHelpers\Utility\MathematicalExpressions;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3Fluid\Fluid\Core\Variables\ScopedVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;


final class ForViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;
    
    public function initializeArguments(): void
    {
        $this->registerArgument('array', 'array', 'The array to iterate over', true);
        $this->registerArgument('variables', 'mixed', 'Forward variables');
        $this->registerArgument('index', 'string', 'The starting index. Can be a raw integer number or a mathematical expression.', false, '0');
        $this->registerArgument('condition', 'string', 'The break condition. Can be a raw integer number or a mathematical expression', false, '-1');
        $this->registerArgument('operator', 'string', 'The operator to compare the index and the break condition operand. Possible operators are <, >, <=, >=, ==, !=', false, '<');
    }

    public function render(): string
    {
        $globalVariableProvider = $this->renderingContext->getVariableProvider();
        $localVariableProvider = new StandardVariableProvider();
        //dump($this->arguments['array']);
        dump($globalVariableProvider);
        dump($localVariableProvider);
        dump($this->arguments['variables']);
        
        $localVariableProvider = new StandardVariableProvider();
        $this->renderingContext->setVariableProvider(new ScopedVariableProvider($globalVariableProvider, $localVariableProvider));
        dump($localVariableProvider);
        return '';
    }
}

?>