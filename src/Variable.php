<?php

namespace ether\seo;

use craft\elements\Asset;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use ether\seo\fields\SeoField;

class Variable {

	public function custom (
		$title = '',
		$description = '',
		$includeTitleSuffix = true,
		$social = []
	) {
		$settings = Seo::$i->getSettings();
		$socialImage = $this->_socialImageFallback();

		$text = [
			'title' =>
				$title
					? $title . (
					$includeTitleSuffix
						? ' ' . $settings['titleSuffix']
						: ''
					) : '',
			'description' => $description ?: '',
		];

		$ret = $text;
		$ret['social'] = SeoField::$defaultValue['social'];

		foreach ($social as $key => $value)
		{
			$ret['social'][$key] = array_merge(
				$ret['social'][$key],
				$value
			);

			if (!$ret['social'][$key]['image'] && $socialImage)
				$ret['social'][$key]['image'] = $socialImage;
		}

		return $ret;
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
		$socialImage = $this->_socialImageFallback();

		if (!array_key_exists('social', $value)) {
			return SeoField::$defaultValue['social'];
		}

		foreach ($value['social'] as $name => $v) {
			$social[$name] = [
				'title' => $v['title'] ?: $value['title'],
				'image' => $v['image'] ?: $socialImage,
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
	 * @return \Twig_Markup|string
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
	 * @return \Twig_Markup|string
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
	 * @return \Twig_Markup|string
	 * @throws \yii\base\Exception
	 */
	private function _socialImage ($image, array $transform)
	{
		if (!$image) return '';

		$transformUrl = $image->getUrl($transform);

		if ($transformUrl && strpos($transformUrl, 'http') === false)
			$transformUrl = UrlHelper::siteUrl($transformUrl);

		return Template::raw($transformUrl);
	}

	/**
	 * Returns fallback image from global settings
	 *
	 * @return Asset|null
	 */
	private function _socialImageFallback ()
	{
		static $fallback = null;

		if (!$fallback)
		{
			$settings = Seo::$i->getSettings();
			if (!empty($settings['socialImage']))
				$fallback = \Craft::$app->assets->getAssetById((int) $settings['socialImage'][0]);
		}

		return $fallback;
	}

}