<?php

namespace ether\seo\controllers;

use craft\web\Controller;
use ether\seo\Seo;
use ether\seo\web\assets\SeoSettingsAsset;
use yii\web\HttpException;

class SitemapController extends Controller
{

	/**
	 * @throws HttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex ()
	{
		$currentUser = \Craft::$app->user;
		if (!$currentUser->checkPermission('manageSitemap') && !$currentUser->getIsAdmin())
			throw new HttpException(403);

		$namespace = 'data';

		$this->view->registerAssetBundle(SeoSettingsAsset::class);
		$this->view->registerJs(
			"new SeoSettings('{$namespace}', 'sitemap');"
		);

		return $this->renderTemplate('seo/sitemap', [
			'namespace' => $namespace,
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],

			'sitemap' => Seo::$i->sitemap->getSitemap(),
			'sections' => Seo::$i->sitemap->getValidSections(),
			'categories' => Seo::$i->sitemap->getValidCategories(),
			'productTypes' => Seo::$i->sitemap->getValidProductTypes(),
		]);
	}

	/**
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;

		$data = $craft->request->getRequiredBodyParam('data');

		if (Seo::$i->sitemap->saveSitemap($data))
		{
			$craft->session->setNotice(
				\Craft::t('seo', 'Sitemap Updated')
			);
		}
		else
		{
			$craft->session->setNotice(
				\Craft::t('seo', 'Couldn\'t save sitemap')
			);
		}


		$this->redirectToPostedUrl();
	}

}