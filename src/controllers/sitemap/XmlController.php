<?php

namespace ether\seo\controllers\sitemap;

use craft\web\Controller;
use ether\seo\Seo;
use yii\web\Response;

class XmlController extends Controller
{

	protected $allowAnonymous = true;

	public function beforeAction($action)
	{
		$siteHandle = \Craft::$app->getRequest()->getQueryParam("site");
		if (!empty($siteHandle))
			\Craft::$app->sites->setCurrentSite($siteHandle);

		return parent::beforeAction($action);
	}

	/**
	 * @throws \yii\base\Exception
	 */
	public function actionIndex ()
	{
		return $this->_asXml(Seo::$i->sitemap->index());
	}

	public function actionCore ()
	{
		return $this->_asXml(
			Seo::$i->sitemap->core(\Craft::$app->urlManager->getRouteParams())
		);
	}

	public function actionCustom ()
	{
		return $this->_asXml(Seo::$i->sitemap->custom());
	}

	// Helpers
	// =========================================================================

	private function _asXml ($data)
	{
		$response = \Craft::$app->getResponse();
		$response->content = $data;
		$response->format = Response::FORMAT_XML;

		return $response;
	}

}
