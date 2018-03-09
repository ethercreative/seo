<?php

namespace ether\seo\controllers;

use craft\web\Controller;
use ether\seo\Seo;
use yii\web\HttpException;

class SettingsController extends Controller
{

	/**
	 * @throws HttpException
	 */
	public function actionIndex ()
	{
		$currentUser = \Craft::$app->user;
		if (!$currentUser->getIsAdmin())
			throw new HttpException(403);

		$settings = Seo::$i->getSettings();
		$settings->validate();

		$fullPageForm = true;

		$crumbs = [
			['label' => 'SEO', 'url' => 'index'],
		];

		$tabs = [
			[
				'label' => 'Fieldtype',
				'url'   => "#settings-fieldtype",
				'class' => null,
			],
			[
				'label' => 'Robots',
				'url'   => "#settings-robots",
				'class' => null,
			],
			[
				'label' => 'Sitemap',
				'url'   => "#settings-sitemap",
				'class' => null,
			],
		];

		$this->renderTemplate('seo/settings', array_merge(
			compact('settings', 'fullPageForm', 'crumbs', 'tabs'),
			Seo::getFieldTypeSettingsVariables()
		));
	}

}