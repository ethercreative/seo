<?php

namespace ether\seo\web\assets;

use craft\web\AssetBundle;

class SeoSettingsAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->js = [
			'js/SeoSettings.min.js',
			'js/SeoFieldSettings.min.js',
		];

		$this->css = [
			'css/seo.css',
			'css/settings.css',
		];

		parent::init();
	}

}