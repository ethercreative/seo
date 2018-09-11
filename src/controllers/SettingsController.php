<?php

namespace ether\seo\controllers;

use craft\web\Controller;
use ether\seo\Seo;
use ether\seo\web\assets\SeoSettingsAsset;
use yii\web\HttpException;

class SettingsController extends Controller
{

	/**
	 * @throws HttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex ()
	{
		$currentUser = \Craft::$app->user;
		if (!$currentUser->getIsAdmin())
			throw new HttpException(403);

		$settings = Seo::$i->getSettings();
		$settings->validate();

		$namespace = 'settings';

		$this->view->registerAssetBundle(SeoSettingsAsset::class);
		$this->view->registerJs(
			"new SeoSettings('{$namespace}', 'settings');new SeoFieldSettings('{$namespace}')"
		);

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
			[
				'label' => 'Social',
				'url'   => "#settings-social",
				'class' => null,
			],
		];

		$this->renderTemplate('seo/settings', array_merge(
			compact(
				'settings',
				'namespace',
				'fullPageForm',
				'crumbs',
				'tabs'
			),
			Seo::getFieldTypeSettingsVariables()
		));
	}

}