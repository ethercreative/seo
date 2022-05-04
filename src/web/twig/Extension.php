<?php
/**
 * SEO for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\web\twig;

use ether\seo\models\data\SeoData;
use Twig\Extension\AbstractExtension as TwigExtension;
use Twig\TwigFunction;

/**
 * Class Extension
 *
 * @author  Ether Creative
 * @package ether\seo\web\twig
 */
class Extension extends TwigExtension
{

	public function getFunctions ()
	{
		return [
			new TwigFunction(
				'getSeoField',
				[$this, 'getSeoField'],
				['needs_context' => true]
			),
		];
	}

	// Functions
	// =========================================================================

	public function getSeoField ($ctx, $handle = 'seo')
	{
		try {
			$seo = null;

			if (isset($ctx[$handle]))
				$seo = $ctx[$handle];

			elseif (isset($ctx['entry']) && isset($ctx['entry'][$handle]))
				$seo = $ctx['entry'][$handle];

			elseif (isset($ctx['product']) && isset($ctx['product'][$handle]))
				$seo = $ctx['product'][$handle];

			elseif (isset($ctx['category']) && isset($ctx['category'][$handle]))
				$seo = $ctx['category'][$handle];

			if ($seo instanceof SeoData)
				return $seo;

		} catch (\Exception $e) {
			return null;
		}

		return null;
	}

}