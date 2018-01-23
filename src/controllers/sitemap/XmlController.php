<?php

namespace ether\seo\controllers\sitemap;

use craft\web\Controller;
use ether\seo\Seo;

class XmlController extends Controller
{

	protected $allowAnonymous = true;

	/**
	 * @throws \yii\base\Exception
	 */
	public function actionIndex ()
	{
		$this->_setHeaders();
		echo Seo::$i->sitemap->index();
	}

	/**
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionCore ()
	{
		$this->_setHeaders();
		echo Seo::$i->sitemap->core(\Craft::$app->request->queryParams);
	}

	public function actionCustom ()
	{
		$this->_setHeaders();
		echo Seo::$i->sitemap->custom();
	}

	// Helpers
	// =========================================================================

	private function _setHeaders ()
	{
		\Craft::$app->request->headers->fromArray([
			'content-type' => 'xml',
			'charset' => 'utf-8',
		]);
	}

}