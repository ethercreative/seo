<?php

namespace ether\seo\web\assets;

use craft\web\AssetBundle;

class SeoFieldAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->js = [
			'js/seo/textarea.js',
			'js/seo/field/index.js',
			'js/seo/tabs/index.js',
			'js/seo/social-card/index.js',
		];

		$this->jsOptions = [
			'type' => 'module',
		];

		$this->css = [
			'css/seo.css', // old
			'css/seo-field.css',
		];

		parent::init();
	}

}
