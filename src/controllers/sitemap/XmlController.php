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
		exit();
	}

	public function actionCore ()
	{
		$this->_setHeaders();
		echo Seo::$i->sitemap->core(\Craft::$app->urlManager->getRouteParams());
		exit();
	}

	public function actionCustom ()
	{
		$this->_setHeaders();
		echo Seo::$i->sitemap->custom();
		exit();
	}

	// Helpers
	// =========================================================================

	private function _setHeaders ()
	{
		\Craft::$app->getResponse()->headers->fromArray([
			'content-type' => 'xml',
			'charset' => 'utf-8',
		]);
	}

}