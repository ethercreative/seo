<?php

namespace ether\seo\web\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class RedirectsAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

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