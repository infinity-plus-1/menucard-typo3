<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\BitmapIconProvider;

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

return [
	"textpicture_icon" => [
		"provider" => BitmapIconProvider::class,
		"source" => "EXT:menucardpackage/Resources/Public/cb/textpicture/Icons/cb_icon.jpg",
	],
	"menucards_icon" => [
		"provider" => BitmapIconProvider::class,
		"source" => "EXT:menucardpackage/Resources/Public/cb/menucards/Icons/cb_icon.jpg",
	],
	"blog_creator_icon" => [
		"provider" => BitmapIconProvider::class,
		"source" => "EXT:menucardpackage/Resources/Public/cb/blog_creator/Icons/cb_icon.jpg",
	],
];