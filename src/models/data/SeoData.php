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
use craft\web\View;
use ether\seo\fields\SeoField;
use ether\seo\models\Settings;
use ether\seo\Seo;
use yii\base\BaseObject;

/**
 * Class SeoData
 *
 * @property string|array $title
 * @property string       $description
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

	/** @var array|string */
	public $titleRaw = [];

	public $descriptionRaw = '';

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

	/** @var string */
	private $_handle;

	/** @var Element */
	private $_element;

	/** @var array */
	private $_fieldSettings;

	/** @var Settings */
	private $_seoSettings;

	/** @var string */
	private $_titleTemplate = '';

	/** @var string */
	private $_renderedTitle;

	/** @var string */
	private $_descriptionTemplate = '';

	/** @var string */
	private $_renderedDescription;

	// Constructor
	// =========================================================================

	public function __construct (SeoField $seo = null, ElementInterface $element = null, array $config = [])
	{
		$this->_handle = $seo !== null ? $seo->handle : null;
		$this->_element = $element;
		$this->_seoSettings = Seo::$i->getSettings();
		$this->_fieldSettings =
			$seo === null
				? SeoField::$defaultFieldSettings
				: $seo->getSettings();

		// Backwards compatibility for titles in SEO v3.4.x or lower
		if (isset($config['title']))
		{
			$title    = $config['title'];
			$template = $this->_getSetting('title');

			// Find the first unlocked token
			if (is_string($title) && !empty($template))
				foreach ($template as $index => $tmpl)
					if ($tmpl['locked'] === '0')
						$title = [$template[$index]['key'] => $title];

			$config['titleRaw'] = $title;
			unset($config['title']);
		}

		// Backwards compatibility for descriptions in SEO v3.4.x or lower
		if (isset($config['description']))
		{
			$config['descriptionRaw'] = $config['description'];
			unset($config['description']);
		}

		// Backwards compatibility for Keywords in SEO v1 / Craft v2
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
		$title    = array_filter($this->titleRaw);
		$template = $this->_getSetting('title');

		if (is_string($title)) $this->_titleTemplate = $title;
		else
		{
			$this->_titleTemplate = implode(
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
		}

		// Description
		// ---------------------------------------------------------------------

		if (!empty($this->descriptionRaw))
			$this->_renderedDescription = $this->descriptionRaw;

		$this->_descriptionTemplate = $this->_getSetting('description');

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
				// FIXME
				$this->social[$key] = new SocialData($key, $fallback, $value);
		}

		// Robots
		// ---------------------------------------------------------------------

		// Filter out empty robots
		$this->advanced['robots'] = array_filter($this->advanced['robots']);
	}

	// Getters / Setters
	// =========================================================================

	/**
	 * @return array|string
	 * @throws \Throwable
	 */
	public function getTitle ()
	{
		if ($this->_renderedTitle)
			return $this->_renderedTitle;

		if ($this->_element === null || $this->_handle === null)
			return '';

		return $this->_renderedTitle = $this->_render(
			$this->_titleTemplate,
			$this->_elementToArray()
		);
	}

	/**
	 * @return array
	 * @throws \Throwable
	 */
	public function getTitleAsTokens ()
	{
		if (
			$this->_element === null
			|| $this->_handle === null
			|| !\Craft::$app->request->isCpRequest
		) return [];

		$template = $this->_getSetting('title');
		$elementArray = $this->_elementToArray();

		$tokens = [];

		foreach ($template as $token)
		{
			$tokens[$token['key']] = $this->_render(
				$token['template'],
				$elementArray
			);
		}

		return $tokens;
	}

	/**
	 * @return string
	 * @throws \Throwable
	 */
	public function getDescription ()
	{
		if ($this->_renderedDescription)
			return $this->_renderedDescription;

		if ($this->_element === null || $this->_handle === null)
			return '';

		return $this->_renderedDescription = $this->_render(
			$this->_descriptionTemplate,
			$this->_elementToArray()
		);
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

	private function _elementToArray ()
	{
		$variables = [];

		foreach ($this->_element->attributes() as $name)
			if ($name !== $this->_handle)
				$variables[$name] = $this->_element->$name;

		return array_merge(
			$variables,
			$this->_element->toArray($this->_element->extraFields())
		);
	}

	/**
	 * @param $template
	 * @param $variables
	 *
	 * @return string
	 * @throws \Throwable
	 */
	private function _render ($template, $variables)
	{
		$craft = \Craft::$app;

		if ($template === null)
			return '';

		try {
			// If this is a CP request, render the title as if it was the frontend
			if ($craft->request->isCpRequest)
			{
				$site   = $craft->sites->currentSite;
				$tpMode = $craft->view->templateMode;
				$craft->sites->setCurrentSite($this->_element->site);
				$craft->view->setTemplateMode(View::TEMPLATE_MODE_SITE);

				$ret = \Craft::$app->view->renderObjectTemplate(
					$template,
					$variables
				);

				$craft->sites->setCurrentSite($site);
				$craft->view->setTemplateMode($tpMode);
			}
			else
			{
				$ret = \Craft::$app->view->renderObjectTemplate(
					$template,
					$variables
				);
			}
		} catch (\Exception $e) {
			$ret = 'ERROR: ' . $e->getMessage();
		}

		return $ret;
	}

}