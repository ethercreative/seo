<?php

namespace ether\seo\services;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use ether\seo\fields\SeoField;
use ether\seo\models\data\SeoData;
use ether\seo\Seo;

class SeoService extends Component
{

	// Actions
	// =========================================================================

	/**
	 * Adds the `X-Robots-Tag` header to the request if needed.
	 */
	public function injectRobots ()
	{
		$headers = \Craft::$app->getResponse()->getHeaders();

		// If devMode always noindex
		if (\Craft::$app->config->general->devMode)
		{
			$headers->set('x-robots-tag', 'none, noimageindex');
			return;
		}

		list($field, $element) = $this->_getElementAndSeoFields();

		// Robots
		$robots = $field->robots;

		if ($robots !== null)
			$headers->set('x-robots-tag', $robots);

		// Get Expiry Date
		/** @var \DateTime $expiry */
		if (isset($element->expiryDate))
			$expiry = $element->expiryDate->format(\DATE_RFC850);
		else
			$expiry = null;

		// If we've got an expiry time, add an additional header
		if ($expiry)
			$headers->add('x-robots-tag', 'unavailable_after: ' . $expiry);
	}

	public function injectCanonical ()
	{
		list($field) = $this->_getElementAndSeoFields();

		\Craft::$app->getResponse()->getHeaders()->add(
			'Link',
			'<' . $field->canonical . '>; rel="canonical"'
		);
	}

	// Helpers
	// =========================================================================

	private function _getElementAndSeoFields ()
	{
		static $element = null;
		static $field = null;

		if ($element !== null)
			return [$field, $element];

		try {
			$resolve = \Craft::$app->request->resolve();
		} catch (\Exception $e) {
			$resolve = [null, []];
		}

		$resolve   = $resolve[1];
		$variables = array_key_exists('variables', $resolve)
			? $resolve['variables']
			: [];
		$handle = null;

		// Get all available "top-level" SEO fields
		foreach ($variables as $variable)
		{
			if (!is_subclass_of($variable, Element::class))
				continue;

			/** @var Element $variable */
			$element = $variable;

			/** @var Field $field */
			foreach ($variable->fieldLayout->getFields() as $field)
			{
				if (get_class($field) !== SeoField::class)
					continue;

				$handle = $field->handle;
				break;
			}

			break;
		}

		if ($handle)
			$field = $element->{$handle};
		else
			$field = new SeoData();

		return [$field, $element];
	}

}
