<?php

namespace ether\seo\controllers;

use Craft;
use craft\web\Controller;
use ether\seo\Seo;
use ether\seo\web\assets\RedirectsAsset;
use Exception;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\StaleObjectException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

class RedirectsController extends Controller
{

	/**
	 * @throws HttpException
	 * @throws InvalidConfigException
	 */
	public function actionIndex ()
	{
		$currentUser = Craft::$app->user;
		if (!$currentUser->checkPermission('manageRedirects') && !$currentUser->getIsAdmin())
			throw new HttpException(403);

		$namespace = 'data';
		$csrfn = Craft::$app->config->general->csrfTokenName;
		$csrf  = Craft::$app->request->csrfToken;

		$this->view->registerAssetBundle(RedirectsAsset::class);
		$this->view->registerJs(
			"new SeoSettings('{$namespace}', 'redirects', ['{$csrfn}', '{$csrf}']);"
		);

		$sites = ['null' => 'All Sites'];
		foreach (Craft::$app->sites->getAllSites() as $site)
			$sites[$site->id] = $site->name;

		return $this->renderTemplate('seo/redirects', [
			'namespace' => $namespace,
			'crumbs' => [
				['label' => 'SEO', 'url' => 'index'],
			],
			'redirects' => Seo::$i->redirects->findAllRedirects(),
			'sites' => $sites,
		]);
	}

	/**
	 * @throws BadRequestHttpException
	 */
	public function actionSave ()
	{
		$this->requirePostRequest();

		$request  = Craft::$app->request;

		$order  = $request->getRequiredBodyParam('order');
		$uri    = $request->getRequiredBodyParam('uri');
		$to     = $request->getRequiredBodyParam('to');
		$type   = $request->getRequiredBodyParam('type');
		$siteId = $request->getBodyParam('siteId', false);
		$id     = $request->getBodyParam('id');

		$err = Seo::$i->redirects->save($order, $uri, $to, $type, $siteId, $id);

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
	 * @throws BadRequestHttpException
	 */
	public function actionBulk ()
	{
		$request  = Craft::$app->request;

		$redirects = $request->getRequiredBodyParam('redirects');
		$separator = $request->getRequiredBodyParam('separator');
		$type      = $request->getRequiredBodyParam('type');
		$siteId    = $request->getRequiredBodyParam('siteId');

		list($success, $error) =
			Seo::$i->redirects->bulk($redirects, $separator, $type, $siteId);

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

	public function actionSort ()
	{
		$order = Craft::$app->getRequest()->getBodyParam('order');

		$error = Seo::$i->redirects->sort($order);

		if ($error) return $this->asErrorJson($error);
		else {
			return $this->asJson([
				'success' => true,
			]);
		}
	}

	/**
	 * @throws Exception
	 * @throws Throwable
	 * @throws StaleObjectException
	 * @throws BadRequestHttpException
	 */
	public function actionDelete ()
	{
		$id = Craft::$app->request->getRequiredBodyParam('id');

		if ($err = Seo::$i->redirects->delete($id))
			return $this->asErrorJson($err);
		else
			return $this->asJson([ 'success' => true ]);
	}

}
