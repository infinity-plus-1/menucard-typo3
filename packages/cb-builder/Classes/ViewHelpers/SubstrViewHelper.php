<?php

declare(strict_types=1);

/**
 * Part of the Cb-Builder
 * 
 * Created by:          Dennis Schwab (dennis.schwab90@icloud.com)
 * Created at:          04.04.2025
 * Last modified by:    -
 * Last modified at:    -
 * Licence: GPL2.0 or later
 * Version: 0.1.0
 */

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

namespace DS\CbBuilder\ViewHelpers;

use InvalidArgumentException;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * View helper to extract a substring from a string at a specific location for a specific length.
 */
final class SubstrViewHelper extends AbstractViewHelper
{
    /**
     * Initializes the arguments for this view helper.
     */
    public function initializeArguments(): void
    {
        $this->registerArgument(
            'value',
            'string',
            'The string to extract from.',
            true
        );
        $this->registerArgument(
            'offset',
            'int',
            'The starting index of the string.',
            true
        );
        $this->registerArgument(
            'length',
            'int',
            'The length of bytes (characters).'
        );
        $this->registerArgument(
            'stripTags',
            'bool',
            'Strips all HTML tags from the string - expands PHP\'s strip_tags function.'
        );
    }

    /**
     * Renders the view helper.
     *
     * @return string The substring of the given string.
     */
    public function render(): string
    {
        $string = $this->arguments['value'] ?? '';
        $offset = $this->arguments['offset'] ?? 0;
        $length = $this->arguments['length'] ?? NULL;
        $stripTags = $this->arguments['stripTags'] ?? NULL;
        if ($stripTags) {
            $string = strip_tags($string);
        }
        return substr($string, $offset, $length);
    }

    /**
     * Returns the name of the content argument.
     *
     * @return string The name of the content argument.
     */
    public function getContentArgumentName(): string
    {
        return 'value';
    }
}