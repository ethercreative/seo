<?php

namespace ether\seo\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\Entry;
use craft\helpers\Json;
use craft\models\Section;
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

	public static $defaultValue = [
		'title'       => '',
		'description' => '',
		'keywords'    => '',
		'score'       => 'neutral',
		'social'      => [
			'twitter'  => ['title' => '', 'image' => null, 'description' => ''],
			'facebook' => ['title' => '', 'image' => null, 'description' => ''],
		],
	];

	// Instance
	// -------------------------------------------------------------------------

	/** @var string */
	public $titleSuffix;

	/** @var mixed */
	public $socialImage;

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
		if (empty($value))
			return self::$defaultValue;

		if (!is_array($value))
			$value = Json::decode($value);

		$social = array_merge(self::$defaultValue['social'], $value['social']);
		foreach ($social as $k => $s)
		{
			if ($s['image'] !== '')
			{
				if (
					is_object($s['image'])
					&& get_class($s['image']) === 'craft\elements\Asset'
				) continue;

				if (is_array($s['image'])) {
					$s['image'] = $s['image']['id'];
				}

				$s['image'] = \Craft::$app->assets->getAssetById(
					(int)$s['image']
				);
			}
			else
			{
				$s['image'] = $this->_socialFallbackImage();
			}

			$social[$k] = $s;
		}

		$value['social'] = $social;

		return $value;
	}

	/**
	 * @param mixed                 $value
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

		if ($hasPreview && $isEntry && $value['title'] === null && $isSingle)
			$titleSuffix = $element->title . ' ' . $titleSuffix;

		// Social URL
		// ---------------------------------------------------------------------

		preg_match(
			"((http?s?:\/\/)?(www.)?(.*)\/)",
			$craft->sites->currentSite->baseUrl,
			$socialPreviewUrl
		);
		$socialPreviewUrl = $socialPreviewUrl[3];

		// Render
		// ---------------------------------------------------------------------

		$seoOptions = Json::encode(compact(
			'hasPreview',
			'isNew'
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
		if (empty($value))
			$value = self::$defaultValue;

		return $value['title'] . ' ' . $value['description'];
	}

	public function getTableAttributeHtml (
		$value,
		ElementInterface $element
	): string {
		if (empty($value))
			$value = self::$defaultValue;

		switch ($value['score']) {
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

	// Helpers
	// =========================================================================

	private function _socialFallbackImage ()
	{
		$assets = \Craft::$app->assets;

		$settings = Seo::$i->getSettings();
		$fieldFallback = $this->getSettings()['socialImage'];

		return !empty($fieldFallback)
			? $assets->getAssetById((int) $fieldFallback[0])
			: !empty($settings['socialImage'])
				? $assets->getAssetById((int) $settings['socialImage'][0])
				: null;
	}

}