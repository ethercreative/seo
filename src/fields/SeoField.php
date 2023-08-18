<?php

namespace ether\seo\fields;

use craft\base\ElementInterface;
use craft\base\Field;
use craft\base\PreviewableFieldInterface;
use craft\elements\Category;
use craft\elements\Entry;
use craft\elements\GlobalSet;
use craft\errors\GqlException;
use craft\gql\TypeLoader;
use craft\helpers\Json;
use craft\helpers\UrlHelper;
use craft\helpers\Html;
use craft\models\Section;
use craft\shopify\elements\Product as ShopifyProduct;
use ether\seo\models\data\SeoData;
use ether\seo\Seo;
use ether\seo\web\assets\SeoFieldAsset;
use ether\seo\web\assets\SeoFieldSettingsAsset;
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

		'title' => [],
		'description' => '',

		'socialImage' => null,
		'hideSocial' => false,
		'robots' => [],
	];

	// Instance
	// -------------------------------------------------------------------------

	/**
	 * @var string
	 * @deprecated
	 */
	public $titleSuffix;

	/**
	 * @var bool
	 * @deprecated
	 */
	public $suffixAsPrefix;

	/** @var array */
	public $title;

	/** @var string */
	public $description;

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

	public function getContentColumnType (): array|string
	{
		return Schema::TYPE_TEXT;
	}

    /**
     * @return array
     */
	public function getContentGqlType (): \GraphQL\Type\Definition\Type|array
    {
        return [
            'name' => $this->handle,
            'type' => \ether\seo\gql\SeoData::getType(),
        ];
    }

	public function normalizeValue (mixed $value, ?\craft\base\ElementInterface $element = null): mixed
	{
		if ($value instanceof SeoData)
			return $value;

		if (is_string($value))
			$value = Json::decodeIfJson($value);

		if (is_string($value))
			$value = ['title' => $value];

		return new SeoData($this, $element, $value ?? []);
	}

	/**
	 * @param SeoData               $value
	 * @param ElementInterface|null $element
	 *
	 * @return string
	 * @throws InvalidConfigException
	 * @throws \Twig\Error\LoaderError
	 * @throws \yii\base\Exception
	 */
	public function getInputHtml (mixed $value, ?\craft\base\ElementInterface $element = null): string
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
		$isCalendar = false;
		$isHome = $element->getIsHomepage();
		$isNew = $element->getId() === null;
		$isSingle = false;
		$previewAction = null;

		switch (get_class($element)) {
			case 'craft\\elements\\Entry':
				/** @var Entry $element */
				try {
					$isEntry = true;
					$section = $element->getSection();
				} catch (InvalidConfigException $e) {}
				$previewAction = $craft->getSecurity()->hashData(
					'entries/preview-entry'
				);
				break;
			case 'craft\\elements\\Category':
				$previewAction = $craft->getSecurity()->hashData(
					'categories/preview-category'
				);
				break;
			case 'craft\\commerce\\elements\\Product':
				$previewAction = $craft->getSecurity()->hashData(
					'commerce/products-preview/preview-product'
				);
				break;
			case 'Solspace\\Calendar\\Elements\\Event':
				/** @var $element \Solspace\Calendar\Elements\Event */
				$isCalendar = true;
				$previewAction = $craft->getSecurity()->hashData(
					'calendar/events/preview'
				);
				$hasPreview = \Solspace\Calendar\Calendar::getInstance()->calendars->isEventTemplateValid(
					$element->getCalendar(),
					$element->siteId
				);
				break;
			default:
				/** @var ElementInterface $element */
		}

		if ($section) {
            $hasPreview = !empty($section->previewTargets) && $previewAction !== null;

			$isSingle = $section->type === Section::TYPE_SINGLE;
		}

		// URL & Title Suffix
		// ---------------------------------------------------------------------

		$titleTemplate = $settings['title'] ?? $settingsGlobal['title'];

		$url = $element->getUrl();
		$slug = $element->slug;

		if ($hasPreview && $isEntry && ! $isHome && ! $isSingle && $element->slug) {
			$url = substr($url, 0, strrpos($url, $element->slug));
		}


		// Social URL
		// ---------------------------------------------------------------------

		$socialPreviewUrl = null;

		if ($craft->sites->currentSite->baseUrl) {
			preg_match(
				"((http?s?:\/\/)?(www.)?(.*)\/)",
				\Craft::parseEnv($craft->sites->currentSite->baseUrl),
				$socialPreviewUrl
			);
			$socialPreviewUrl = $socialPreviewUrl[3] ?? '';
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

		$renderData = [
			'elementType' => get_class($element),
			'elementId' => $element->id,
			'siteId' => $element->siteId,
			'seoHandle' => $this->handle,
		];

		if ($element instanceof Category)
			$renderData['groupId'] = $element->groupId;
		elseif ($element instanceof GlobalSet || $element instanceof ShopifyProduct)
			$renderData['typeId'] = null;
		elseif ($isCalendar)
			$renderData['calendarId'] = $element->calendarId;
		else
			$renderData['typeId'] = $element->typeId;

		$seoOptions = Json::encode(compact(
			'hasPreview',
			'previewAction',
			'isNew',
			'renderData'
		));

		$craft->view->registerAssetBundle(SeoFieldAsset::class);
		$craft->view->registerJs(
			"new SeoField('{$namespaceId}', {$seoOptions})"
		);

		return $craft->view->renderTemplate(
			'seo/_seo/fieldtype',
			[
				'id' => $this->id,
				'name' => $this->handle,
				'value' => $value,
				'titleTemplate' => $titleTemplate,
				'hasPreview' => $hasPreview,
				'url' => $url,
				'slug' => $slug,
				'isPro' => true,

				'isNew' => $isNew,
				'isHome' => $isHome,
				'isSingle' => $isSingle,

				'siteUrl' => UrlHelper::siteUrl(),
				'socialPreviewUrl' => $socialPreviewUrl,
				'hideSocial' => $hideSocial,

				'defaultRobots' => $defaultRobots,
			]
		);
	}

	/**
	 * @return null|string
	 * @throws \Twig\Error\LoaderError
	 * @throws \yii\base\Exception
	 */
	public function getSettingsHtml (): ?string
	{
		$view = \Craft::$app->view;
		$namespace = $view->getNamespace();
		$namespaceId = Html::namespaceId('', $namespace);

		$view->registerAssetBundle(SeoFieldSettingsAsset::class);
		$view->registerJs('new SeoFieldSettings("' . $namespaceId . '");');

		return $view->renderTemplate(
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

	public function getSearchKeywords (mixed $value, ElementInterface $element): string {
		/** @var SeoData $value */
		return $value->title . ' ' . $value->description;
	}

	public function getTableAttributeHtml (
		mixed $value,
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
