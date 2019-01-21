<?php
/**
 * SEO for Craft
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2019 Ether Creative
 */

namespace ether\seo\console\controllers;

use yii\console\Controller;
use yii\console\ExitCode;
use ether\seo\Seo;

/**
 * Class UpgradeController
 *
 * @author  Ether Creative
 * @package ether\seo\console\controllers
 */
class UpgradeController extends Controller
{

	/**
	 * Triggers the upgrade to the new data format
	 */
	public function actionToNewDataFormat ()
	{
		Seo::getInstance()->upgrade->toNewDataFormat();

		return ExitCode::OK;
	}

}