<?php
/**
 * SEO for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\models\data;

use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\Json;
use ether\seo\fields\SeoField;
use ether\seo\models\Settings;
use ether\seo\Seo;

/**
 * Class SeoData
 *
 * @author  Ether Creative
 * @package ether\seo\models\data
 */
class SeoData extends BaseDataModel
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var string */
	public $title = '';

	/** @var string */
	public $description = '';

	/** @var array */
	public $keywords = [];

	/** @var string */
	public $score = 'neutral';

	/** @var array */
	public $social = [
		'twitter'  => null,
		'facebook' => null,
	];

	/** @var array */
	public $advanced = [
		'robots' => [],
	];

	// Properties: Private
	// -------------------------------------------------------------------------

	/** @var Element */
	private $_element;

	/** @var array */
	private $_fieldSettings;

	/** @var Settings */
	private $_seoSettings;

	// Constructor
	// =========================================================================

	public function __construct (SeoField $seo = null, ElementInterface $element = null, array $config = [])
	{
		$this->_element = $element;
		$this->_seoSettings = Seo::$i->getSettings();
		$this->_fieldSettings =
			$seo === null
				? SeoField::$defaultFieldSettings
				: $seo->getSettings();

		// Backwards compatibility for SEO v1 / Craft v2
		if (isset($config['keyword']))
		{
			if (!empty($config['keyword'])) {
				$config['keywords'] = [[
					'keyword' => $config['keyword'],
					'rating' => [
						''     => 'neutral',
						'good' => 'good',
						'ok'   => 'average',
						'bad'  => 'poor',
					][$config['score']],
				]];
			}

			unset($config['keyword']);
		}

		// Decode keywords JSON string (when saving)
		if (isset($config['keywords']) && is_string($config['keywords']))
			$config['keywords'] = Json::decodeIfJson($config['keywords']);

		// Merge social w/ defaults
		if (isset($config['social']))
		{
			$this->social = array_merge($this->social, $config['social']);
			unset($config['social']);
		}

		// Merge advanced w/ defaults
		if (isset($config['advanced']))
		{
			$this->advanced = array_merge($this->advanced, $config['advanced']);
			unset($config['advanced']);
		}

		parent::__construct($config);
	}

	// Init
	// =========================================================================

	public function init ()
	{
		// Title
		// ---------------------------------------------------------------------

		$titleSuffix = $this->_fieldSettings['titleSuffix'] ?: $this->_seoSettings['titleSuffix'];
		$suffixAsPrefix = $this->_fieldSettings['suffixAsPrefix'];

		if ((empty($this->title) || $this->title === $titleSuffix) && $this->_element !== null)
		{
			if ($suffixAsPrefix)
				$this->title = $titleSuffix . ' ' . $this->_element->title;
			else
				$this->title = $this->_element->title . ' ' . $titleSuffix;
		}

		// Keywords
		// ---------------------------------------------------------------------

		if (!is_array($this->keywords))
			$this->keywords = [];

		// Social
		// ---------------------------------------------------------------------

		$fallback = $this->_getSocialFallback();

		foreach ($this->social as $key => $value)
		{
			if ($value === null)
				$this->social[$key] = new SocialData($key, $fallback);
			elseif (is_array($value))
				$this->social[$key] = new SocialData($key, $fallback, $value);
		}

		// Robots
		// ---------------------------------------------------------------------

		// Filter out empty robots
		$this->advanced['robots'] = array_filter($this->advanced['robots']);
	}

	// Helpers
	// =========================================================================

	/**
	 * Gets the social metadata fallback
	 *
	 * @return array
	 */
	private function _getSocialFallback ()
	{
		$image = null;

		$assets = \Craft::$app->assets;

		$fieldFallback = $this->_fieldSettings['socialImage'];

		if (!empty($fieldFallback))
			$image = $assets->getAssetById((int)$fieldFallback[0]);

		else {
			$seoFallback = $this->_seoSettings['socialImage'];

			if (!empty($seoFallback))
				$image = $assets->getAssetById((int)$seoFallback[0]);
		}

		return [
			'title'       => $this->title,
			'description' => $this->description,
			'image'       => $image,
		];
	}

}