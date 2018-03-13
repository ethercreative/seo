<?php

namespace ether\seo\resources;

use craft\web\AssetBundle;

class SeoFieldSettingsAssets extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = '@ether/seo/resources';

		$this->js = [
			'js/SeoSettings.min.js',
		];

		$this->css = [
			'css/seo.css',
			'css/settings.css',
		];

		parent::init();
	}

}