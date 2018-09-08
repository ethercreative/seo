<?php
/**
 * SEO for Craft
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\resources;

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
		$this->sourcePath = '@ether/seo/resources';

		$this->js = [
			'js/SeoFieldSettings.min.js',
		];

		$this->css = [
			'css/settings.css',
		];

		parent::init();
	}

}