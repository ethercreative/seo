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

}