<?php

namespace menucardvendor\menucardpackage\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

final class HelloViewHelper extends AbstractViewHelper
{
    public function initializeArguments(): void
    {
        $this->registerArgument('greetingText', 'string', 'Create your sweety greeting text', false, 'Hello %%PLACEHOLDER%%, how are you today?');
        $this->registerArgument('placeholder', 'string', 'Define a placeholder yourself', false, '%%PLACEHOLDER%%');
        $this->registerArgument('name', 'string', 'Here comes your name', true);
    }

    public function render(): string
    {
        $greetingText = $this->arguments['greetingText'];
        $placeholder = $this->arguments['placeholder'];
        $name = $this->arguments['name'];
        $greetingText = str_replace($placeholder, $name, $greetingText);
        return $greetingText;
    }
}

?>