<?php

namespace ether\seo\services;

use craft\base\Component;
use craft\base\Element;
use craft\base\Field;
use craft\db\Migration;
use craft\db\Query;
use craft\helpers\Json;
use ether\seo\fields\SeoField;
use ether\seo\Seo;

class SeoService extends Component
{

	/**
	 * Adds the `X-Robots-Tag` header to the request if needed.
	 */
	public function injectRobots ()
	{
		try {
			$resolve = \Craft::$app->request->resolve();
		} catch (\Exception $e) {
			$resolve = [null, []];
		}

		$headers = \Craft::$app->getResponse()->getHeaders();
		$resolve = $resolve[1];
		$variables = array_key_exists('variables', $resolve)
			? $resolve['variables']
			: [];

		// If devMode always noindex
		if (\Craft::$app->config->general->devMode)
		{
			$headers->set('x-robots-tag', 'none, noimageindex');
			return;
		}

		$robots = [];
		$expiry = null;

		// Get all available "top-level" SEO fields
		foreach ($variables as $variable)
		{
			if (!is_subclass_of($variable, Element::class))
				continue;

			/** @var Element $variable */

			/** @var Field $field */
			foreach ($variable->fieldLayout->getFields() as $field)
				if (get_class($field) === SeoField::class)
					$robots = array_merge(
						$robots,
						$variable->{$field->handle}->advanced['robots']
					);
			
			/** @var \DateTime $expiry */
			if (isset($variable->expiryDate))
				$expiry = $variable->expiryDate->format(\DATE_RFC850);
			else
				$expiry = null;
		}

		// If we don't have any variables (i.e. when just rendering a template)
		// fallback to the site-wide robots settings
		if (empty($variables))
			$robots = Seo::$i->getSettings()->robots;

		// Remove empties and duplicates (on the off-chance)
		if (is_array($robots))
			$robots = array_filter(array_unique($robots));

		// If we've got robots, add the header
		if (!empty($robots))
			$headers->set('x-robots-tag', implode(', ', $robots));

		// If we've got an expiry time, add an additional header
		if ($expiry)
			$headers->add('x-robots-tag', 'unavailable_after: ' . $expiry);
	}

}
