<?php

namespace Craft;

class Seo_AbService extends BaseApplicationComponent {

	// Variables
	// =========================================================================

	static private $ab = null;

	// Public
	// =========================================================================

	/**
	 * Gets the current sessions A/B value
	 *
	 * @return int - 0 == B, 1 == A
	 */
	public function getAb ()
	{
		if (self::$ab != null) return self::$ab;

		$cookie = craft()->request->getCookie("seo_ab");

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

		$cookie = new HttpCookie("seo_ab", "");
		$cookie->value = craft()->security->hashData(
			base64_encode(serialize($ab))
		);
		// Expire in ~1 month
		$cookie->expire = time() + 86400 * 30;
		$cookie->path = "/";

		craft()->request->getCookies()->add($cookie->name, $cookie);

		return $ab;
	}

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
		$fieldLayoutId = $elements[0]->getFieldLayout()->id;
		$fields = $this->_getEnabledFieldsFromLayoutId($fieldLayoutId);

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

	// Private
	// =========================================================================

	/**
	 * Returns an array of enabled fields from the given layout (ID)
	 *
	 * [fieldId => [handle => "", type => BaseFieldType], ... ]
	 *
	 * @param int $layoutId
	 *
	 * @return array
	 */
	private function _getEnabledFieldsFromLayoutId ($layoutId)
	{
		$fieldIds =
			craft()->db->createCommand()
			           ->select("fieldId")
			           ->from("seo_ab_fields")
			           ->where("layoutId = :layoutId", compact("layoutId"))
			           ->queryAll();

		// Map [["fieldId" => int], ... ] to [int, ... ]
		$fieldIds = array_map(function ($field) {
			return $field["fieldId"];
		}, $fieldIds);

		// Get the layouts fields
		$layoutFields = craft()->fields->getLayoutFieldsById($layoutId);

		// Reduce to only the fields that have A/B enabled, and
		return array_reduce(
			$layoutFields,
			function (array $fields, FieldLayoutFieldModel $f) use ($fieldIds) {
				if (!in_array($f->fieldId, $fieldIds)) return;

				$field = $f->getField();
				$fields[$field->id] = [
					"handle" => $field->handle,
					"type"   => $field->getFieldType(),
				];
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
		$elementIds = implode(",", $elementIds);

		$locale = craft()->locale->id;

		$data =
			craft()->db->createCommand()
			           ->select("elementId, fieldId, data")
			           ->from("seo_ab_data")
			           ->where("elementId IN :elementIds", compact("elementIds"))
			           ->andWhere("locale = :locale", compact("locale"))
			           ->queryAll();

		// Map [["elementId" => int, ... ], ... ]
		// to
		// [elementId => [fieldId => data, ... ], ... ]
		return array_reduce(
			$data,
			function (array $mappedData, array $row) {
				list($elementId, $fieldId, $data) = $row;

				if (!array_key_exists($elementId, $data))
					$data[$elementId] = [];

				$mappedData[$elementId][$fieldId] = $data;
			},
			[]
		);
	}

}