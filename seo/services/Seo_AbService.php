<?php

namespace Craft;

class Seo_AbService extends BaseApplicationComponent {

	static private $ab = null;

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
		$fieldLayout = $elements[0]->getFieldLayout();
		// TODO: Lookup layout ID & field IDs
		// TODO: Map field IDs to their handles for later use

		// Get the ID's of the elements
		$ids = array_map(function (BaseElementModel $element) {
			return $element->id;
		}, $elements);

		// TODO: Get all stored B data for each element ID

		// TODO: Loop through each element, then each elements A/B enabled
		// TODO[cont.]: fields and check to see if we have B data for that
		// TODO[cont.]: field. If we do, replace the data on the element.
	}

}