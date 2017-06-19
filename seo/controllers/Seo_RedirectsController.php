<?php

namespace Craft;

class Seo_RedirectsController extends BaseController
{

	public function actionSaveRedirects()
	{
		$this->requirePostRequest();

		if (craft()->seo_redirect->saveAllRedirects(craft()->request->getRequiredPost('data'))) {
			craft()->userSession->setNotice(Craft::t('Redirects updated.'));
		} else {
			craft()->userSession->setError(Craft::t('Couldn’t update redirects.'));
		}

		$this->redirectToPostedUrl();
	}

	public function actionAddRedirect ()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$uri = craft()->request->getRequiredPost("uri");
		$to = craft()->request->getRequiredPost("to");
		$type = craft()->request->getRequiredPost("type");

		$err = craft()->seo_redirect->save($uri, $to, $type);
		if (!is_numeric($err)) {
			$this->returnErrorJson($err);
		} else {
			$this->returnJson([
				"success" => true,
			    "id" => $err,
			]);
		}
	}

	public function actionUpdateRedirect ()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost("id");
		$uri = craft()->request->getRequiredPost("uri");
		$to = craft()->request->getRequiredPost("to");
		$type = craft()->request->getRequiredPost("type");

		if ($err = craft()->seo_redirect->update($id, $uri, $to, $type)) {
			$this->returnErrorJson($err);
		} else {
			$this->returnJson([ "success" => true ]);
		}
	}

	public function actionRemoveRedirect ()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$id = craft()->request->getRequiredPost("id");

		if ($err = craft()->seo_redirect->delete($id)) {
			$this->returnErrorJson($err);
		} else {
			$this->returnJson([ "success" => true ]);
		}
	}

	public function actionBulkAddRedirects ()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$redirects = craft()->request->getRequiredPost("redirects");
		$separator = craft()->request->getRequiredPost("separator");
		$type = craft()->request->getRequiredPost("type");

		list($success, $error) = craft()->seo_redirect->bulk($redirects, $separator, $type);

		if ($error) {
			$this->returnErrorJson($error);
		} else {
			$this->returnJson([
				"success" => true,
			    "redirects" => $success,
			]);
		}
	}

}