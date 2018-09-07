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
use yii\base\BaseObject;

/**
 * Class SeoData
 *
 * @author  Ether Creative
 * @package ether\seo\models\data
 */
class SeoData extends BaseObject
{

	// Properties
	// =========================================================================

	// Properties: Public
	// -------------------------------------------------------------------------

	/** @var array */
	public $title = [];

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

		$twig     = \Craft::$app->view->twig;
		$title    = $this->title;
		$template = $this->_getSetting('title');

		// Backwards compatibility for SEO v3.4.* or below
		if (is_string($title) && !empty($template))
			foreach ($template as $index => $tmpl)
				if ($tmpl['locked'] === '0')
					$title = [$template[$index]['key'] => $title];

		$this->title = implode(
			'',
			array_map(
				function ($a) use ($twig, $title) {
					return array_key_exists($a['key'], $title)
						? twig_escape_filter($twig, $title[$a['key']])
						: $a['template'];
				},
				$template
			)
		);

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

	private function _getSetting ($handle)
	{
		return empty($this->_fieldSettings[$handle])
			? $this->_seoSettings[$handle]
			: $this->_fieldSettings[$handle];
	}

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