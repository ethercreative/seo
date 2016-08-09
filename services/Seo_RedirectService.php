<?php

namespace Craft;

class Seo_RedirectService extends BaseApplicationComponent
{

	public function getAllRedirects ()
	{
		return Seo_RedirectRecord::model()->findAll();
	}

	public function saveAllRedirects ($data)
	{
		$oldRedirects = $this->getAllRedirects();
		$newRedirects = json_decode($data['redirects'], true);

		// Delete removed redirects
		$newById = [];
		$oldById = [];

		$newRecordsRaw = [];

		foreach ($newRedirects as $new)
		{
			if ($new['id'] !== -1) $newById[$new['id']] = $new;
			else $newRecordsRaw[] = $new;
		}

		$idsToDelete = [];
		foreach ($oldRedirects as $old)
		{
			if (array_key_exists($old['id'], $newById)) {
				$oldById[$old['id']] = $old;
			} else {
				$idsToDelete[] = $old['id'];
			}
		}

		if (!empty($idsToDelete)) {
			craft()->db->createCommand()->delete('seo_redirects', array('in', 'id', $idsToDelete));
		}

		// Update current redirects
		foreach ($newById as $new)
		{
			$old = $oldById[$new['id']];

			if (
				$old['uri'] !== $new['uri'] ||
				$old['to'] !== $new['to'] ||
				$old['type'] !== $new['type']
			) {
				$old->setAttribute('uri', $new['uri']);
				$old->setAttribute('to', $new['to']);
				$old->setAttribute('type', $new['type']);
				$old->save();
			}
		}

		// Add new redirects
		foreach ($newRecordsRaw as $new)
		{
			$record = new Seo_RedirectRecord();
			$record->setAttribute('uri', $new['uri']);
			$record->setAttribute('to', $new['to']);
			$record->setAttribute('type', $new['type']);
			$record->save();
		}

		// TODO: Add redirects to .htaccess / web.config to improve performance

		return true;
	}

	public function findRedirectByPath ($path)
	{
		$redirects = $this->getAllRedirects();

		foreach ($redirects as $redirect)
		{
			$to = false;

			if (trim($redirect['uri'], '/') == $path)
			{
				$to = $redirect['to'];
			}
			elseif ($uri = $this->_isRedirectRegex($redirect['uri']))
			{
				if(preg_match($uri, $path)){
					$to = preg_replace($uri, $redirect['to'], $path);
				}
			}

			if ($to)
			{
				return [
					'to' => strpos($to, '://') !== false ? $to : UrlHelper::getSiteUrl($to),
					'type' => $redirect['type']
				];
			}
		}

		return false;
	}

	private function _isRedirectRegex ($uri)
	{
		if (preg_match("/^#(.+)#$/", $uri))
		{
			return $uri;
		}
		elseif (strpos($uri, "*"))
		{
			return "#^".str_replace(array("*","/"), array("(.*)", "\/"), $uri).'#';
		}

		return false;
	}

}