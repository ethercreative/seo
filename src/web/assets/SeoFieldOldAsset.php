<?php

namespace ether\seo\web\assets;

use craft\web\AssetBundle;

class SeoFieldOldAsset extends AssetBundle
{

	public function init ()
	{
		$this->sourcePath = __DIR__;

		$this->js = [
			'js/SeoField.min.js',
		];

		$this->css = [
			'css/seo.css',
		];

		parent::init();
	}

}
