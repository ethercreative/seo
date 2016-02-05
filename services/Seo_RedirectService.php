<?php

namespace Craft;

class Seo_RedirectService extends BaseApplicationComponent
{

	public function updateRedirects ($data)
	{
		$settings = craft()->seo->settings();

		$redirects = $data['redirects'] ?: array();
		$publicPath = $this->_parsePublicPath($settings->publicPath);

		switch ($settings->redirectMethod) {
			case 'ht':
				$this->_updateHtaccess($redirects, $publicPath);
				break;
			case 'wc':
				$this->_updateWebConfig($redirects, $publicPath);
				break;
		}
	}

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

	private function _parsePublicPath ($path)
	{
		foreach (craft()->config->get('environmentVariables') as $key => $var)
		{
			$path = str_replace('{' . $key . '}', $var, $path);
		}

		 return substr($path, -1) == '/' ? $path : $path . '/';
	}

	private function _updateHtaccess ($redirects, $publicPath)
	{
		if (!$file = IOHelper::getFile($publicPath . '.htaccess')) {
			IOHelper::touch($publicPath . '.htaccess');
			$file = IOHelper::getFile($publicPath . '.htaccess');
		}

		$originalContents = $file->getContents();

		$contents = preg_replace('/#SEO_REDIRECTS_START#[\s\S]+?#SEO_REDIRECTS_END#/', '', $originalContents);

		$redirectContents = "#SEO_REDIRECTS_START#\r\n";
		$redirectContents .= "<IfModule mod_rewrite.c>\r\n";
		$redirectContents .= "    RewriteEngine On\r\n\r\n";

		foreach ($redirects as $redirect)
		{
			$to = $redirect['to'];
			$to = strpos($to, 'http') === false && substr($to, 0) !== '/' ? '/' . $to : $to;

			if ($uri = $this->_isRedirectRegex($redirect['uri']))
			{
				$uri = stripcslashes(str_replace('#', '', $uri));
			}
			else
			{
				$uri = $redirect['uri'];
				$uri = substr($uri, 0) !== '^' ? '^' . $uri : $uri;
				$uri = substr($uri, -1) === '/' ? $uri . '/?$' : $uri . '$';
			}

			$redirectContents .= "    RewriteRule {$uri} {$to} [R={$redirect['type']},L]\r\n";
		}

		$redirectContents .= "</IfModule>\r\n";
		$redirectContents .= "#SEO_REDIRECTS_END#" . (strpos($originalContents, '#SEO_REDIRECTS_START#') !== false ? '' : "\r\n\r\n");

		$file->write($redirectContents . $contents, false);
	}

	private function _updateWebConfig ($redirects, $publicPath)
	{
		// TODO
	}

}