<?php

declare(strict_types=1);

namespace DS\fluidHelpers\ViewHelpers;


use Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class InnerTextViewHelperException extends Exception {}

/**
 * Strip html tags around text.
 * 
 * Use:
 * 
 * Input:
 * Fluid variable: ['myText': '< p>< strong>Inner text< /strong>< /p>']
 * < fh:innerText>{myText}< /fh:innerText>
 * 
 * Output: Inner text
 * 
 * @param string value The text to strip.
 * 
 */
final class InnerTextViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments(): void
    {
        $this->registerArgument('value', 'string', 'The html to extract the text from.');
    }

    public function render(): string
    {
        $html = $this->arguments['value'] ?? ($this->renderChildren() ?? NULL);
        return trim(strip_tags(htmlspecialchars_decode($html)));
    }

    public function getContentArgumentName(): string
    {
        return 'value';
    }

}