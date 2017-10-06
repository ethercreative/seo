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
	public function inject (array $elements)
	{
		// If this is an A session (or there aren't any elements)
		// we don't need to do anything
		if ($this->getAb() || empty($elements)) return;

		// Check to see if we've got any fields with A/B enabled
		$fields = $this->_getEnabledFieldsFromLayoutId(
			$elements[0]->getFieldLayout()
		);

		// If we don't have any enabled fields
		if (empty($fields)) return;

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
				list($handle, $type) = $fields[$fieldId];
				$element->getContent()->$handle = $type->prepValue($data);
			}
		}
	}

	// Events
	// -------------------------------------------------------------------------

	/**
	 * Fired when elements in the CP are populated
	 *
	 * @param BaseElementModel[] $elements
	 */
	public function onPopulateElements (array $elements)
	{
		if (empty($elements)) return;

		// Store the field layout id of the first element
		SeoPlugin::$possibleFieldLayoutIds[] =
			$elements[0]->getFieldLayout()->id;
	}

	/**
	 * Called when a layout is saved
	 *
	 * @param FieldLayoutModel $layout
	 */
	public function onFieldLayoutSave (FieldLayoutModel $layout)
	{
		$layoutId = $layout->id;

		$nextFieldIds = craft()->request->getPost('seoAB');
		if (!$nextFieldIds) return;
		$prevFieldIds = $this->_getEnabledFieldsFromLayoutId($layout, true);

		$addedIds = array_map(function ($fieldId) use ($layoutId) {
			return [$layoutId, $fieldId];
		}, array_diff($nextFieldIds, $prevFieldIds));

		$removedIds = array_diff($prevFieldIds, $nextFieldIds);

		// Insert new
		if (!empty($addedIds)) {
			craft()->db->createCommand()->insertAll(
				'seo_ab_fields',
				['layoutId', 'fieldId'],
				$addedIds,
				false
			);
		}

		// Remove old
		if (!empty($removedIds)) {
			craft()->db->createCommand()->delete(
				'seo_ab_fields',
				[
					'and',
					['layoutId = :layoutId', compact('layoutId')],
					['in', 'fieldId', $removedIds]
				]
			);
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
	 * @param bool             $idsOnly
	 *
	 * @return array
	 */
	private function _getEnabledFieldsFromLayoutId (
		FieldLayoutModel $layout,
		$idsOnly = false
	) {
		$layoutId = $layout->id;

		$fieldIds =
			craft()->db->createCommand()
			           ->select('fieldId')
			           ->from('seo_ab_fields')
			           ->where('layoutId = :layoutId', compact('layoutId'))
			           ->queryAll();

		// Map [['fieldId' => int], ... ] to [int, ... ]
		$fieldIds = array_map(function ($field) {
			return $field['fieldId'];
		}, $fieldIds);

		if ($idsOnly) return $fieldIds;

		// Reduce to only the fields that have A/B enabled, and
		return array_reduce(
			$layout->getFields(),
			function (array $fields, FieldLayoutFieldModel $f) use ($fieldIds) {
				if (!in_array($f->fieldId, $fieldIds)) return $fields;

				$field = $f->getField();
				$fields[$field->id] = [
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
			           ->where('elementId IN :elementIds', compact('elementIds'))
			           ->andWhere('locale = :locale', compact('locale'))
			           ->queryAll(false);

		// Map [['elementId' => int, ... ], ... ]
		// to [elementId => [fieldId => data, ... ], ... ]
		return array_reduce(
			$data,
			function (array $mappedData, array $row) {
				list($elementId, $fieldId, $data) = $row;

				if (!array_key_exists($elementId, $data))
					$data[$elementId] = [];

				$mappedData[$elementId][$fieldId] = $data;

				return $mappedData;
			},
			[]
		);
	}

}
