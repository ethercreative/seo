<?php

namespace ether\seo\controllers;

use craft\helpers\DateTimeHelper;
use craft\web\Controller;
use ether\seo\Seo;

class SeoController extends Controller
{

	protected array|int|bool $allowAnonymous = ['robots'];

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

	/**
	 * @throws \yii\web\BadRequestHttpException
	 * @throws \yii\base\InvalidConfigException
	 * @throws \Exception
	 */
	public function actionRenderData ()
	{
		$this->requirePostRequest();
		$craft = \Craft::$app;

		$elementType = $craft->request->getBodyParam('elementType');
		$elementId   = $craft->request->getBodyParam('elementId', null);
		$siteId      = $craft->request->getBodyParam('siteId');
		$seoHandle   = $craft->request->getBodyParam('seoHandle');

		// Get the element
		if ($elementId) {
			$element = $craft->elements->getElementById(
				$elementId,
				$elementType,
				$siteId
			);

			if (!$element)
				return $this->asJson([]);
		} else {
			$element = new $elementType();
		}

		// Populate the data
		$body = $craft->request->getBodyParams();
		foreach ($body as $prop => $value)
		{
			if (!property_exists($element, $prop))
				continue;

			if (in_array($prop, ['postDate', 'expiryDate'])) {
                $dateTime = DateTimeHelper::toDateTime($value);
                if ($dateTime !== false) {
                    $element->$prop = DateTimeHelper::toDateTime($value);
                }
            } else {
                $element->$prop = $value;
            }
		}

		$element->setFieldValuesFromRequest(
			$craft->request->getParam('fieldsLocation', 'fields')
		);

		return $this->asJson($element->$seoHandle->titleAsTokens);
	}

}