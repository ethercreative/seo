<?php

namespace ether\seo\controllers;

use craft\web\Controller;

class SeoController extends Controller
{

	public function actionIndex ()
	{
		$this->renderTemplate('seo/index');
	}

}