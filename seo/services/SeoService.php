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

}