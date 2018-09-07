<?php

namespace ether\seo\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\Entry;
use craft\helpers\Json;
use craft\models\Section;
use ether\seo\models\data\SeoData;
use ether\seo\resources\SeoFieldAssets;
use ether\seo\resources\SeoFieldSettingsAssets;
use ether\seo\Seo;
use yii\base\InvalidConfigException;
use yii\db\Schema;

class SeoField extends Field implements PreviewableFieldInterface
{

	// Props
	// =========================================================================

	// Static
	// -------------------------------------------------------------------------

	public static $defaultFieldSettings = [
		'titleSuffix' => null,
		'suffixAsPrefix' => false,
		'socialImage' => null,
		'hideSocial' => false,
		'robots' => [],
	];

	// Instance
	// -------------------------------------------------------------------------

	/** @var string */
	public $titleSuffix;

	/** @var bool */
	public $suffixAsPrefix;

	/** @var mixed */
	public $socialImage;

	/** @var boolean */
	public $hideSocial;

	/** @var array */
	public $robots;

	// Public Functions
	// =========================================================================

	// Static
	// -------------------------------------------------------------------------

	public static function displayName (): string
	{
		return \Craft::t('seo', 'SEO');
	}

	public static function hasContentColumn (): bool
	{
		return true;
	}

	// Instance
	// -------------------------------------------------------------------------

	public function getContentColumnType (): string
	{
		return Schema::TYPE_TEXT;
	}

	public function normalizeValue ($value, ElementInterface $element = null)
	{
		if ($value instanceof SeoData)
			return $value;

		if (is_string($value))
			$value = Json::decodeIfJson($value);

		return new SeoData($this, $element, $value ?? []);
	}

	/**
	 * @param SeoData               $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws InvalidConfigException
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function getInputHtml ($value, ElementInterface $element = null): string
	{
		if (!$element) return '';

		// Variables
		// ---------------------------------------------------------------------
		$craft = \Craft::$app;
		$namespaceId = $craft->view->namespaceInputId($this->id);

		$settings = $this->getSettings();
		$settingsGlobal = Seo::$i->getSettings();

		$hasPreview = false;
		$section = null;
		$isEntry = false;
		$isHome = false;
		$isNew = $element->getId() === null;
		$isSingle = false;

		switch (get_class($element)) {
			case 'craft\\elements\\Entry':
				/** @var Entry $element */
				try {
					$isEntry = true;
					$section = $element->getSection();
				} catch (InvalidConfigException $e) {}
				break;
			default:
				/** @var ElementInterface $element */
		}

		if ($section) {
			$hasPreview = $craft->sections->isSectionTemplateValid(
				$section,
				$element->siteId
			);

			$isSingle = $section->type === Section::TYPE_SINGLE;
		}

		// URL & Title Suffix
		// ---------------------------------------------------------------------

		$url = $element->getUrl();

		if ($hasPreview && $isEntry && !$isHome && !$isSingle)
			$url = substr($url, 0, strrpos( $url, '/')) . '/';

		if ($element->slug)
			$url = str_replace($element->slug, '', $url);

		$titleSuffix = $settings['titleSuffix'] ?: $settingsGlobal['titleSuffix'];
		$suffixAsPrefix = $settings['suffixAsPrefix'];

		if ($hasPreview && $isEntry && $value->title === null && $isSingle)
		{
			if ($suffixAsPrefix)
				$titleSuffix = $titleSuffix . ' ' . $element->title;
			else
				$titleSuffix = $element->title . ' ' . $titleSuffix;
		}

		// Social URL
		// ---------------------------------------------------------------------

		if ($craft->sites->currentSite->baseUrl) {
			preg_match(
				"((http?s?:\/\/)?(www.)?(.*)\/)",
				$craft->sites->currentSite->baseUrl,
				$socialPreviewUrl
			);
			$socialPreviewUrl = $socialPreviewUrl[3];
		}

		// Advanced
		// ---------------------------------------------------------------------

		$defaultRobots = array_key_exists('robots', $settings)
			? $settings['robots']
			: [];

		// Render
		// ---------------------------------------------------------------------

		$hideSocial = array_key_exists('hideSocial', $settings)
			? $settings['hideSocial']
			: false;

		$seoOptions = Json::encode(compact(
			'hasPreview',
			'isNew',
			'suffixAsPrefix'
		));

		$craft->view->registerAssetBundle(SeoFieldAssets::class);
		$craft->view->registerJs(
			"new SeoField('{$namespaceId}', {$seoOptions})"
		);

		return $craft->view->renderTemplate(
			'seo/_seo/fieldtype',
			[
				'id' => $this->id,
				'name' => $this->handle,
				'value' => $value,
				'titleSuffix' => $titleSuffix,
				'hasPreview' => $hasPreview,
				'url' => $url,
				'isPro' => true,

				'isNew' => $isNew,
				'isHome' => $isHome,
				'isSingle' => $isSingle,

				'socialPreviewUrl' => $socialPreviewUrl,
				'hideSocial' => $hideSocial,

				'defaultRobots' => $defaultRobots,
			]
		);
	}

	/**
	 * @return null|string
	 * @throws \Twig_Error_Loader
	 * @throws \yii\base\Exception
	 */
	public function getSettingsHtml ()
	{
		\Craft::$app->view->registerAssetBundle(SeoFieldSettingsAssets::class);

		return \Craft::$app->view->renderTemplate(
			'seo/_seo/settings',
			array_merge(
				[
					'settings' => $this,
					'globalSettings' => Seo::$i->getSettings(),
				],
				Seo::getFieldTypeSettingsVariables()
			)
		);
	}

	public function getSearchKeywords ($value, ElementInterface $element): string {
		/** @var SeoData $value */
		return $value->title . ' ' . $value->description;
	}

	public function getTableAttributeHtml (
		$value,
		ElementInterface $element
	): string {
		/** @var SeoData $value */
		switch ($value->score) {
			case 'poor':
				return '<span class="status active" style="margin-top:5px;background:#ff4750;" title="Poor"></span>';
				break;
			case 'average':
				return '<span class="status active" style="margin-top:5px;background:#ffab47;" title="Average"></span>';
				break;
			case 'good':
				return '<span class="status active" style="margin-top:5px;background:#3eda80;" title="Good"></span>';
				break;
			default:
				return '<span class="status active" style="margin-top:5px;background:#ccc;" title="Unranked"></span>';
		}
	}

}