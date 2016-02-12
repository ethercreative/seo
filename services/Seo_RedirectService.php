<?php

namespace Craft;

class Seo_RedirectService extends BaseApplicationComponent
{

	public function findRedirectByPath ($path)
	{
		$redirects = craft()->seo->getData('redirects') ? craft()->seo->getData('redirects')['redirects'] : array();

		foreach ($redirects as $redirect)
		{
			$to = false;

			if ($redirect['uri'] == $path)
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
					'to' => strpos($to, 'http') !== false ? $to : UrlHelper::getSiteUrl($to),
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