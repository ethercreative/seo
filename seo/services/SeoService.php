<?php

namespace Craft;

class SeoService extends BaseApplicationComponent
{

	public function settings ()
	{
		return craft()->plugins->getPlugin('seo')->getSettings();
	}

	public function saveData ($name, $data)
	{
		if (!$record = SeoRecord::model()->findByPk($name)) {
			$record = new SeoRecord();
			$record->setAttribute('name', $name);
		}

		$record->setAttribute('data', $data);

		if ($record->save()) {
			return true;
		} else {
			return false;
		}
	}

	public function getData ($name)
	{
		if ($record = SeoRecord::model()->findByPk($name)) {
			return $record->getAttribute('data');
		} else {
			return array();
		}
	}

	/**
	 * Render the SEO meta template, and inject at the hook
	 *
	 * @param $context
	 *
	 * @return string
	 */
	public function hook (&$context)
	{
		$metaTemplateName = $this->settings()['metaTemplate'];

		if ($metaTemplateName) {
			return craft()->templates->render(
				$metaTemplateName,
				$context
			);
		} else {
			$oldTemplateMode = craft()->templates->getTemplateMode();
			craft()->templates->setTemplateMode(TemplateMode::CP);
			$rendered = craft()->templates->render(
				'seo/_seoDefaultMeta',
				$context
			);
			craft()->templates->setTemplateMode($oldTemplateMode);
			return $rendered;
		}
	}

}