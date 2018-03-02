<?php

namespace Craft;

// TODO: Tidy

class SeoFieldType extends BaseFieldType implements IPreviewableFieldType {

	// Variables
	// =========================================================================

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

	// Methods
	// =========================================================================

	// Methods: Meta
	// -------------------------------------------------------------------------

	public function getName()
	{
		return Craft::t('SEO');
	}

	public function defineContentAttribute()
	{
		return AttributeType::Mixed;
	}

	// Methods: Settings
	// -------------------------------------------------------------------------

	protected function defineSettings()
	{
		return array(
			'titleSuffix' => [AttributeType::String],
			'socialImage' => [AttributeType::Mixed],
			'hideSocial' => [AttributeType::Bool],
		);
	}

	public function getSettingsHtml ()
	{
		craft()->templates->includeJsResource('seo/js/SeoSettings.min.js');

		return craft()->templates->render(
			'seo/_seo/settings',
			array_merge(
				[
					'settings'       => $this->getSettings(),
					'globalSettings' =>
						craft()->plugins->getPlugin('seo')->getSettings(),
				],
				SeoPlugin::getFieldTypeSettingsVariables()
			)
		);
	}

	// Methods: Input / Value
	// -------------------------------------------------------------------------

	public function getInputHtml($name, $value)
	{
		if (empty($this->element)) return '';

		// Variables
		// ---------------------------------------------------------------------
		$id = craft()->templates->formatInputId($name);
		$namespaceId = craft()->templates->namespaceInputId($id);

		$settings = $this->getSettings();
		$settingsGlobal = craft()->plugins->getPlugin('seo')->getSettings();

		$section = null;

		if ($this->element->getElementType() == ElementType::Entry) {
			/** @var SectionModel $section */
			$section = $this->element->getSection();
		}

		$hasPreview = false;
		$isEntry = false;
		$isHome = $this->element->uri == '__home__';
		$isNew = $this->element->getTitle() == null;
		$isSingle = $section ? $section->type == 'single' : true;

		// Backwards compatibility
		// ---------------------------------------------------------------------

		// Convert keyword -> keywords
		if ($value && array_key_exists('keyword', $value)) {
			if (!empty($value['keyword'])) {
				$value['keywords'] = [
					[
						'keyword' => $value['keyword'],
						'rating'  => $this->_scoreCompat($value['score']),
					],
				];
			} else {
				$value['keywords'] = [];
			}

			unset($value['keyword']);

			$value['keywords'] = JsonHelper::encode($value['keywords']);

			// TODO: Rename score to rating
			$value['score'] = 'neutral';
		}

		// Meta
		// ---------------------------------------------------------------------

		// TODO: Handle category entry type
		// TODO: Add hook for handling of custom element types

		switch ($this->element->getElementType()) {
			case ElementType::Entry:
				$isEntry = true;
				$hasPreview = craft()->sections->isSectionTemplateValid($this->element->section);
				break;
			case 'Commerce_Product':
				$hasPreview = craft()->commerce_productTypes->isProductTypeTemplateValid($this->element->type);
				break;
		}

		// Note: Keep in sync with default opts in SeoField.js
		$seoOptions = JsonHelper::encode([
			'hasPreview' => $hasPreview,
			'isNew' => $isNew,
		]);

		craft()->templates->includeCssResource('seo/css/seo.css');
		craft()->templates->includeJsResource('seo/js/SeoField.min.js');
		craft()->templates->includeJs("new SeoField('{$namespaceId}', {$seoOptions});");

		$url = $this->element->getUrl();

		if ($hasPreview && $isEntry && !$isHome && !$isSingle)
			$url = substr($url, 0, strrpos( $url, '/')) . '/';

		$titleSuffix = $settings->titleSuffix ?: $settingsGlobal->titleSuffix;

		if ($hasPreview && $isEntry && $value['title'] == null && $isSingle)
			$titleSuffix = $this->element->title . ' ' . $titleSuffix;

		try {
			$hideSocial = $settings->hideSocial ?: $settingsGlobal->hideSocial;
		} catch (\Exception $e) {
			$hideSocial = false;
		}

		// Social: Site URL
		// ---------------------------------------------------------------------
		preg_match(
			"((http?s?:\/\/)?(www.)?(.*)\/)",
			craft()->siteUrl,
			$socialPreviewUrl
		);
		$socialPreviewUrl = $socialPreviewUrl[3];


		// Return
		// =====================================================================
		return craft()->templates->render('seo/_seo/fieldtype', array(
			'id' => $id,
			'name' => $name,
			'value' => $value,
			'titleSuffix' => $titleSuffix,
			'hasSection' => $hasPreview,
			'url' => $url,
			'isPro' => true,

			'isNew' => $isNew,
			'isHome' => $isHome,
			'isSingle' => $isSingle,

			'socialPreviewUrl' => $socialPreviewUrl,
			'hideSocial' => $hideSocial,
		));
	}

	public function getTableAttributeHtml($value)
	{
		$ret = '';

		switch ($value['score']) {
			case '':
				$ret = '<span class="status active" style="margin-top:5px;background:#ccc;" title="Unranked"></span>';
				break;
			case 'poor':
				$ret = '<span class="status active" style="margin-top:5px;background:#ff4750;" title="Poor"></span>';
				break;
			case 'average':
				$ret = '<span class="status active" style="margin-top:5px;background:#ffab47;" title="Average"></span>';
				break;
			case 'good':
				$ret = '<span class="status active" style="margin-top:5px;background:#3eda80;" title="Good"></span>';
				break;
		}

		return $ret;
	}

	public function prepValue ($value)
	{
		if (empty($value))
			return self::$defaultValue;

		$socialDefaults = self::$defaultValue['social'];

		if (array_key_exists('social', $value)) {
			$social = array_merge($socialDefaults, $value['social']);
			foreach ($social as $k => $s) {
				if ($s['image'] !== '') {
					$s['image'] = craft()->assets->getFileById($s['image']);
				} else {
					$s['image'] = $this->_socialFallbackImage();
				}

				$social[$k] = $s;
			}
			$value['social'] = $social;
		} else {
			$value['social'] = $socialDefaults;
		}

		return $value;
	}

	// Helpers
	// =========================================================================

	/**
	 * Just to make my life harder, I've changed the scores :D
	 *
	 * @param string $score
	 *
	 * @return string
	 */
	private function _scoreCompat ($score) {
		return [
			'' => 'neutral',
			'good' => 'good',
			'ok' => 'average',
			'bad' => 'poor',
        ][$score];
	}

	private function _socialFallbackImage ()
	{
		$settings = craft()->plugins->getPlugin('seo')->getSettings();

		$fieldFallback = $this->getSettings()['socialImage'];

		return !empty($fieldFallback)
			? craft()->assets->getFileById($fieldFallback[0])
			: !empty($settings['socialImage'])
				? craft()->assets->getFileById($settings['socialImage'][0])
				: null;
	}

}
