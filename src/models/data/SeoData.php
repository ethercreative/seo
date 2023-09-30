<?php
/**
 * SEO for Craft CMS
 *
 * @link      https://ethercreative.co.uk
 * @copyright Copyright (c) 2018 Ether Creative
 */

namespace ether\seo\models\data;

use Craft;
use craft\base\Element;
use craft\base\ElementInterface;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\web\View;
use ether\seo\fields\SeoField;
use ether\seo\models\Settings;
use ether\seo\Seo;
use Twig\Markup;
use yii\base\BaseObject;
use yii\base\Exception;

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
		'canonical' => null,
	];

	// Properties: Private
	// -------------------------------------------------------------------------

	/** @var string */
	private $_handle;

	/** @var Element */
	private $_element;

	/** @var array */
	private $_overrideObject = [];

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
		// TODO: There is a LOT going on here, needs to be improved

		$this->_handle = $seo !== null ? $seo->handle : null;
		$this->_element = $element;
		$this->_seoSettings = Seo::$i->getSettings();
		$this->_fieldSettings =
			$seo === null
				? SeoField::$defaultFieldSettings
				: $seo->getSettings();

		// Backwards compatibility for titles in SEO v3.4.x or lower
		if (
			isset($config['titleRaw']) &&
			is_array($config['titleRaw']) &&
			isset($config['titleRaw'][0])
		) {
			$config['titleRaw'] = $config['titleRaw'][0];
		}

		if (
			isset($config['titleRaw']) &&
			(
				($hasTitle = isset($config['title'])) ||
				!is_array($config['titleRaw'])
			)
		) {
			$template = $this->_getSetting('title');
			$title    = $hasTitle ? $config['title'] : $config['titleRaw'];

			// Find the first unlocked token
			if (is_string($title) && !empty($template))
			{
				foreach ($template as $index => $tmpl)
				{
					if ($tmpl['locked'] != false && $tmpl['locked'] !== '0')
						continue;

					$title = [$template[$index]['key'] => $title];
					break;
				}
			}

			$config['titleRaw'] = $title;
			unset($config['title']);
		}

		if (array_key_exists('title', $config))
		{
			$config['titleRaw'] = $config['title'];
			unset($config['title']);
		}

		if (array_key_exists('titleRaw', $config))
		{
			if (is_string($config['titleRaw']))
				$config['titleRaw'] = [$config['titleRaw']];
			else
				$config['titleRaw'] = array_filter($config['titleRaw']);
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

		// Override Object
		if (isset($config['overrideObject']))
		{
			$this->_overrideObject = $config['overrideObject'];
			unset($config['overrideObject']);
		}

		parent::__construct($config);
	}

	// Init
	// =========================================================================

	public function init ()
	{

		// Title
		// ---------------------------------------------------------------------

		$twig     = Craft::$app->view->twig;
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

		// Fallback if robots empty
		if (empty($this->advanced['robots']))
			$this->advanced['robots'] = Seo::$i->getSettings()->robots ?? [];

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
		if (!$this->_renderedTitle) {
			$this->_renderedTitle = $this->_render(
				$this->_titleTemplate,
				$this->_getVariables()
			);
		}

		return new Markup($this->_renderedTitle, 'utf8');
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
			|| !Craft::$app->request->isCpRequest
		) return [];

		$template = $this->_getSetting('title');
		$elementArray = $this->_getVariables();

		$tokens = [];

		foreach ($template as $token)
		{
			$tokens[$token['key']] = new Markup(
				$this->_render(
					$token['template'],
					$elementArray
				),
				'utf8'
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
		if (!$this->_renderedDescription) {
			$this->_renderedDescription = $this->_render(
				$this->_descriptionTemplate,
				$this->_getVariables()
			);
		}

		return new Markup($this->_renderedDescription, 'utf8');
	}

	/**
	 * @return string
	 */
	public function getRobots ()
	{
		if (Craft::$app->config->general->devMode)
			return 'none, noimageindex';

		if (!empty($this->advanced['robots']))
			return implode(', ', $this->advanced['robots']);

		return null;
	}

	/**
	 * @return string|null
	 */
	public function getExpiry ()
	{
		if (!$this->_element || !isset($this->_element->expiryDate))
			return null;

		return $this->_element->expiryDate->format(DATE_RFC850);
	}

	/**
	 * Returns the canonical URL (falling back to the current URL if not set)
	 *
	 * @return string
	 * @throws Exception
	 */
	public function getCanonical ()
	{
		if (empty($this->advanced['canonical']))
			return UrlHelper::siteUrl(Craft::$app->request->getFullPath());

		return UrlHelper::siteUrl($this->advanced['canonical']);
	}

	public function getAbsolute ()
	{
		$url = filter_var(Craft::$app->getRequest()->getAbsoluteUrl(), FILTER_SANITIZE_URL);
		$query = parse_url($url, PHP_URL_QUERY);
		parse_str($query, $parts);

		if (empty($parts)) return $url;

		// Remove token param
		unset($parts[Craft::$app->getConfig()->general->tokenParam]);

		return rtrim(preg_replace('/\?([^#]*)/m', '?' . http_build_query($parts), $url), '?');
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

		if (Craft::$app->getRequest()->getIsSiteRequest())
		{
			$assets        = Craft::$app->assets;
			$fieldFallback = $this->_fieldSettings['socialImage'];

			if (!empty($fieldFallback))
			{
				$image = $assets->getAssetById((int) $fieldFallback[0]);
			}
			else
			{
				$seoFallback = $this->_seoSettings['socialImage'];

				if (!empty($seoFallback))
					$image = $assets->getAssetById((int) $seoFallback[0]);
			}
		}

		return [
			'title'       => $this->title,
			'description' => $this->description,
			'image'       => $image,
		];
	}

	/**
	 * Returns an array for variables for rendering
	 *
	 * @return array
	 */
	private function _getVariables ()
	{
		$variables = $this->_overrideObject;

		if ($this->_element !== null)
		{
			foreach (array_keys($this->_element->fields()) as $name)
				if ($name !== $this->_handle)
					$variables[$name] = $this->_element->$name ?? null;

			if (!array_key_exists('type', $variables) && $this->_element->hasMethod('getType'))
				$variables['type'] = $this->_element->getType();

			if (!array_key_exists('section', $variables) && $this->_element->hasMethod('getSection'))
				$variables['section'] = $this->_element->getSection();

			$variables = array_merge(
				$variables,
				$this->_element->toArray($this->_element->extraFields())
			);
		}

		return $variables;
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
		$craft = Craft::$app;

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

				$ret = $this->_renderObjectTemplate(
					$template,
					$variables
				);

				$craft->sites->setCurrentSite($site);
				$craft->view->setTemplateMode($tpMode);
			}
			else
			{
				$ret = $this->_renderObjectTemplate(
					$template,
					$variables
				);
			}

		} catch (\Exception $e) {
			$ret = 'ERROR: ' . $e->getMessage();
		}

		return $ret;
	}

	private function _renderObjectTemplate (string $template, mixed $object): string
	{
		$str = Craft::$app->view->renderObjectTemplate($template, $object);

		// Craft trims whitespace which isn't what we want, so we'll restore it
		preg_match_all('/^(?<s>\s+)|(?<e>\s+)$/m', $template, $matches, PREG_SET_ORDER);

		return @$matches[0]['s'] . $str . @$matches[1]['e'];
	}

}
