<?php
/**
 * SEO for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\models\data;

use yii\BaseYii;

/**
 * Class BaseDataModel
 *
 * @author  Ether Creative
 * @package ether\seo\models\data
 */
abstract class BaseDataModel
{

	public function __construct ($config = [])
	{
		if (!empty($config))
			BaseYii::configure($this, $config);

		$this->init();
	}

	public abstract function init ();

}