<?php

namespace Craft;

class Seo_AbService extends BaseApplicationComponent {

	// Variables
	// =========================================================================

	static private $ab = null;

	// Public
	// =========================================================================

	// Get / Set AB
	// -------------------------------------------------------------------------

	/**
	 * Gets the current sessions A/B value
	 *
	 * @return int - 0 == B, 1 == A
	 */
	public function getAb ()
	{
		if (self::$ab != null) return self::$ab;

		$cookie = craft()->request->getCookie('seo_ab');

		if (
			$cookie
			&& !empty($cookie->value)
		    && ($ab = craft()->security->validateData($cookie->value)) != false
		) {
			self::$ab = @unserialize(base64_decode($ab));
		} else {
			self::$ab = $this->setAb();
		}

		return self::$ab;
	}

	/**
	 * Sets the current sessions A/B value
	 *
	 * @return int - 0 == B, 1 == A
	 */
	public function setAb ()
	{
		$ab = rand(0, 1);

		$cookie = new HttpCookie('seo_ab', '');
		$cookie->value = craft()->security->hashData(
			base64_encode(serialize($ab))
		);
		// Expire in ~1 month
		$cookie->expire = time() + 86400 * 30;
		$cookie->path = '/';

		craft()->request->getCookies()->add($cookie->name, $cookie);

		return $ab;
	}

	// Injection
	// -------------------------------------------------------------------------

	/**
	 * Injects the AB values into an array of elements
	 *
	 * @param BaseElementModel[] $elements
	 */
	public function injectBElement (array $elements)
	{
		// If this is an A session (or there aren't any elements)
		// we don't need to do anything
		if ($this->getAb() || empty($elements)) return;

		$fields = $this->_getFieldsFromLayout($elements[0]->getFieldLayout());

		// Get the ID's of the elements
		$ids = array_map(function (BaseElementModel $element) {
			return $element->id;
		}, $elements);

		// Get the b data
		$bData = $this->_getBDataForElements($ids);

		// Replace the data
		foreach ($elements as $element) {
			if (!array_key_exists($element->id, $bData)) continue;

			foreach ($bData[$element->id] as $fieldId => $data) {
				/** @var BaseFieldType $type */
				$field = $fields[$fieldId];
				$handle = $field['handle'];
				$type = $field['type'];
				$element->getContent()->$handle = $type->prepValue($data);
			}
		}
	}

	/**
	 * Injects AB edit stuff into edit pages
	 *
	 * @param $context
	 *
	 * @return null|string
	 */
	public function injectElementEdit (&$context)
	{
		/** @var BaseElementModel $element */
		$element = null;

		if (array_key_exists('entry', $context)) {
			$element = $context['entry'];
		} else if (array_key_exists('category', $context)) {
			$element = $context['category'];
		} else if (array_key_exists('product', $context)) {
			$element = $context['product'];
		}

		if ($element == null) return null;

		$this->injectBElement([&$element]);

		$fields = $element->getFieldLayout()->getFields();
		$isEnabled = $this->_isEnabled(
			$element->id,
			$element->getContent()->locale
		);

		craft()->templates->includeJsResource('seo/js/SeoAB.min.js', true);
		craft()->templates->includeJs('new SeoAB();');
		return craft()->templates->render(
			'seo/_ab',
			compact('element', 'fields', 'isEnabled')
		);
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * Fired when an element with SEO A/B capabilities is saved
	 *
	 * @param BaseElementModel $element
	 */
	public function onSaveB (BaseElementModel $element)
	{
		$enabled = craft()->request->getPost('seo_AbEnabled');
		$elementId = $element->id;
		$locale = $element->getContent()->locale;

		// Check if this element-locale was enabled
		$wasEnabled = $this->_isEnabled($elementId, $locale);

		if ($enabled && !$wasEnabled) {
			// If wasn't enabled & is now, insert
			craft()->db->createCommand()
			           ->insert('seo_ab_enabled', compact("elementId", "locale"), false);
		} else if (!$enabled && $wasEnabled) {
			// If was enabled but isn't now, remove
			craft()->db->createCommand()
			           ->delete('seo_ab_enabled', [
				           'and',
				           ['elementId = :elementId', compact('elementId')],
				           ['locale = :locale', compact('locale')]
			           ]);
		}

		if (!$enabled) return;

		$bData = craft()->request->getPost('seoAb');

		if (empty($bData)) return;

		$fieldLayout = $element->getFieldLayout();
		$fields = $this->_getFieldsFromLayout($fieldLayout, true);

		$fieldTypes = array_reduce(
			$fieldLayout->getFields(),
			function (array $types, FieldLayoutFieldModel $field) {
				$field = $field->getField();
				$types[$field->id] = $field->getFieldType();
				return $types;
			},
			[]
		);

		foreach ($bData as $handle => $data) {
			$dataRaw = $data;
			$data = JsonHelper::encode($data);

			$fieldId = $fields[$handle]['id'];

			$key = compact('elementId', 'locale', 'fieldId');
			$update = compact('elementId', 'locale', 'fieldId', 'data');

			$rows = craft()->db->createCommand()
			                   ->insertOrUpdate('seo_ab_data', $key, $update, false);

			if ($rows > 0) {
				/** @var BaseFieldType $type */
				$type = $fieldTypes[$fieldId];
				$type->setElement($element);
				$type->onAfterElementSave();
			}
		}
	}

	// Private
	// =========================================================================

	/**
	 * Returns an array of enabled fields from the given layout
	 *
	 * [fieldId => [handle => '', type => BaseFieldType], ... ]
	 *
	 * @param FieldLayoutModel $layout
	 * @param bool             $handleAsKey
	 *
	 * @return array
	 */
	private function _getFieldsFromLayout (
		FieldLayoutModel $layout,
		$handleAsKey = false
	) {
		// Reduce to only the fields that have A/B enabled, and
		return array_reduce(
			$layout->getFields(),
			function (array $fields, FieldLayoutFieldModel $f) use ($handleAsKey) {
				$field = $f->getField();
				$key = $handleAsKey ? $field->handle : $field->id;
				$fields[$key] = [
					'id'     => $field->id,
					'handle' => $field->handle,
					'type'   => $field->getFieldType(),
				];
				return $fields;
			},
			[]
		);
	}

	/**
	 * Gets all B data for the given element ids
	 *
	 * [elementId => [fieldId => data, ... ], ... ]
	 *
	 * @param int[] $elementIds
	 *
	 * @return array
	 */
	private function _getBDataForElements (array $elementIds)
	{
		$elementIds = implode(',', $elementIds);

		$locale = craft()->locale->id;

		$data =
			craft()->db->createCommand()
			           ->select('elementId, fieldId, data')
			           ->from('seo_ab_data')
			           ->where('elementId IN (:elementIds)', compact('elementIds'))
			           ->andWhere('locale = :locale', compact('locale'))
			           ->queryAll(false);

		// Map [['elementId' => int, ... ], ... ]
		// to [elementId => [fieldId => data, ... ], ... ]
		return array_reduce(
			$data,
			function (array $mappedData, array $row) {
				list($elementId, $fieldId, $data) = $row;

				$data = JsonHelper::decode($data);

				if (!array_key_exists($elementId, $mappedData))
					$mappedData[$elementId] = [];

				$mappedData[$elementId][$fieldId] = $data;

				return $mappedData;
			},
			[]
		);
	}

	/**
	 * Checks if the given element-locale is enabled for A/B
	 *
	 * @param int $elementId
	 * @param string $locale
	 *
	 * @return int
	 */
	private function _isEnabled ($elementId, $locale)
	{
		return
			craft()->db->createCommand()
			           ->select()
			           ->from("seo_ab_enabled")
			           ->where('elementId = :elementId', compact('elementId'))
			           ->andWhere('locale = :locale', compact('locale'))
			           ->count('elementId');
	}

}
