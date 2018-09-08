<?php

namespace ether\seo\resources;

use craft\web\AssetBundle;

class SeoSettingsAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = '@ether/seo/resources';

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