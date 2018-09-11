<?php
/**
 * SEO for Craft
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\web\assets;

use craft\web\AssetBundle;

/**
 * Class SeoFieldSettingsAsset
 *
 * @author  Ether Creative
 * @package ether\seo\resources
 */

class SeoFieldSettingsAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->js = [
			'js/SeoFieldSettings.min.js',
		];

		$this->css = [
			'css/settings.css',
		];

		parent::init();
	}

}