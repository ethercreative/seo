<?php

namespace ether\seo\controllers;

use craft\web\Controller;
use ether\seo\resources\RedirectsAssets;
use ether\seo\Seo;
use yii\web\HttpException;

class RedirectsController extends Controller
{

	/**
	 * @throws HttpException
	 * @throws \yii\base\InvalidConfigException
	 */
	public function actionIndex ()
	{
		$currentUser = \Craft::$app->user;
		if (!$currentUser->checkPermission('manageRedirects') && !$currentUser->getIsAdmin())
			throw new HttpException(403);

		$namespace = 'data';
		$csrfn = \Craft::$app->config->general->csrfTokenName;
		$csrf  = \Craft::$app->request->csrfToken;

		$this->view->registerAssetBundle(RedirectsAssets::class);
		$this->view->registerJs(
			"new SeoSettings('{$namespace}', 'redirects', ['{$csrfn}', '{$csrf}']);"
		);

		return $this->renderTemplate('seo/redirects', [
			'namespace' => $namespace,
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],
			'redirects' => Seo::$i->redirects->findAllRedirects(),
		]);
	}

	/**
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();

		$request  = \Craft::$app->request;

		$uri  = $request->getRequiredBodyParam('uri');
		$to   = $request->getRequiredBodyParam('to');
		$type = $request->getRequiredBodyParam('type');
		$id   = $request->getBodyParam('id');

		$err = Seo::$i->redirects->save($uri, $to, $type, $id);

		if (is_numeric($err))
		{
			return $this->asJson([
				'success' => true,
				'id' => $err,
			]);
		}
		else
		{
			return $this->asErrorJson($err);
		}
	}

	/**
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionBulk ()
	{
		$request  = \Craft::$app->request;

		$redirects = $request->getRequiredBodyParam('redirects');
		$separator = $request->getRequiredBodyParam('separator');
		$type      = $request->getRequiredBodyParam('type');

		list($success, $error) =
			Seo::$i->redirects->bulk($redirects, $separator, $type);

		if ($error)
		{
			return $this->asErrorJson($error);
		}
		else
		{
			return $this->asJson([
				'success' => true,
				'redirects' => $success,
			]);
		}
	}

	/**
	 * @throws \Exception
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 * @throws \yii\web\BadRequestHttpException
	 */
	public function actionDelete ()
	{
		$id = \Craft::$app->request->getRequiredBodyParam('id');

		if ($err = Seo::$i->redirects->delete($id))
			return $this->asErrorJson($err);
		else
			return $this->asJson([ 'success' => true ]);
	}

}