<?php

namespace ether\seo\controllers;

use craft\web\Controller;
use ether\seo\Seo;

class SeoController extends Controller
{

	protected $allowAnonymous = ['robots'];

	public function actionIndex ()
	{
		$this->renderTemplate('seo/index');
	}

	public function actionRobots ()
	{
		$settings = Seo::$i->getSettings();

		\Craft::$app->response->headers->set(
			'Content-Type',
			'text/plain; charset=UTF-8'
		);

		$template = $settings->robotsTxt;
		unset($settings->robotsTxt);

		return $this->asRaw(\Craft::$app->view->renderString($template, [
			'seo' => $settings,
		]));
	}

}