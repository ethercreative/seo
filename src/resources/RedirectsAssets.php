<?php

namespace ether\seo\resources;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RedirectsAssets extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = '@ether/seo/resources';

		$this->depends = [
			CpAsset::class,
		];

		$this->js = [
			'js/SeoSettings.min.js',
		];

		$this->css = [
			'css/redirects.css',
		];

		parent::init();
	}

}