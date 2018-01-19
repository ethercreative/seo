<?php

namespace ether\seo\resources;

use craft\web\AssetBundle;

class SeoFieldAssets extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = '@ether/seo/resources';

		$this->js = [
			'js/SeoField.min.js',
		];

		$this->css = [
			'css/seo.css',
		];

		parent::init();
	}

}