<?php

namespace ether\seo\web\twig;

use Craft;
use craft\elements\Asset;
use craft\helpers\Template;
use craft\helpers\UrlHelper;
use ether\seo\models\data\SeoData;
use Twig\Markup;
use yii\base\Exception;

class Variable
{

	/**
	 * @param string|array $title
	 * @param string $description
	 * @param bool   $_ - Deprecated
	 * @param array  $social
	 *
	 * @return SeoData
	 */
	public function custom (
		$title = '',
		$description = '',
		$_ = null,
		$social = []
	) {
		$config = [
			'titleRaw'    => '',
			'description' => $description,
			'social'      => $social,
		];

		if (is_array($title))
			$config['overrideObject'] = $title;
		else
			$config['titleRaw'] = $title;

		return new SeoData(null, null, $config);
	}

	// Social
	// =========================================================================

	// Social: Images
	// -------------------------------------------------------------------------

	/**
	 * @param Asset|int|string|null $image
	 *
	 * @return Markup|string
	 * @throws Exception
	 */
	public function twitterImage ($image)
	{
		return $this->_socialImage($image, [
			'width'  => 1200,
			'height' => 600,
		]);
	}

	/**
	 * @param Asset|int|string|null $image
	 *
	 * @return Markup|string
	 * @throws Exception
	 */
	public function facebookImage ($image)
	{
		return $this->_socialImage($image, [
			'width'  => 1200,
			'height' => 630,
		]);
	}

	/**
	 * @param Asset|int|string|null $image
	 * @param array      $transform
	 *
	 * @return Markup|string
	 * @throws Exception
	 */
	private function _socialImage ($image, array $transform)
	{
		if (!$image)
			return '';

		if (is_array($image))
			$image = $image['id'];

		if (!($image instanceof Asset))
			$image = Craft::$app->assets->getAssetById((int) $image);

		if (!$image)
			return '';

		$transformUrl = $image->getUrl($transform);

		if ($transformUrl === null)
			return '';

		if ($transformUrl && strpos($transformUrl, 'http') === false)
			$transformUrl = UrlHelper::urlWithScheme($transformUrl, (Craft::$app->getRequest()->getIsSecureConnection()? 'https': 'http'));

		return Template::raw(Craft::parseEnv($transformUrl));
	}

}
