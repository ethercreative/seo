<?php

namespace ether\seo\controllers;

use craft\web\Controller;

class SchemaController extends Controller
{

	public function actionIndex ()
	{
		return $this->renderTemplate('seo/schema', [
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],
		]);
	}

}