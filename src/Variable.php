<?php

namespace ether\seo;

use craft\elements\Asset;
use craft\helpers\UrlHelper;
use ether\seo\fields\SeoField;

class Variable {

	public function custom ($title = '', $description = '', $includeTitleSuffix = true)
	{
		return [
			'title' =>
				$title
					? $title . (
						$includeTitleSuffix
							? ' ' . Seo::$i->getSettings()['titleSuffix']
							: ''
					) : '',
			'description' => $description ?: '',
		];
	}

	// Social
	// =========================================================================

	/**
	 * Gets social values with fallbacks
	 *
	 * @param $value
	 *
	 * @return array
	 */
	public function social ($value)
	{
		$social = [];

		if (!array_key_exists('social', $value)) {
			return SeoField::$defaultValue['social'];
		}

		foreach ($value['social'] as $name => $v) {
			$social[$name] = [
				'title' => $v['title'] ?: $value['title'],
				'image' => $v['image'],
				'description' => $v['description'] ?: $value['description'],
			];
		}

		return $social;
	}

	// Social: Images
	// -------------------------------------------------------------------------

	/**
	 * @param Asset|null $image
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function twitterImage ($image)
	{
		return $this->_socialImage($image, [
			'width'  => 1200,
			'height' => 675,
		]);
	}

	/**
	 * @param Asset|null $image
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function facebookImage ($image)
	{
		return $this->_socialImage($image, [
			'width'  => 1200,
			'height' => 600,
		]);
	}

	/**
	 * @param Asset|null $image
	 * @param array      $transform
	 *
	 * @return string
	 * @throws \yii\base\Exception
	 */
	private function _socialImage ($image, array $transform)
	{
		if (!$image) return '';

		$transformUrl = $image->getUrl($transform);

		if (strpos($transformUrl, 'http') === false)
			$transformUrl = UrlHelper::siteUrl($transformUrl);

		return $transformUrl;
	}

}