<?php

namespace ether\seo\services;

use craft\base\Component;
use craft\events\ExceptionEvent;
use craft\helpers\UrlHelper;
use ether\seo\records\RedirectRecord;
use yii\web\HttpException;

class RedirectsService extends Component
{

	// Public Methods
	// =========================================================================

	// Event Handlers
	// -------------------------------------------------------------------------

	/**
	 * Handles 404 exceptions
	 *
	 * @param ExceptionEvent $event
	 *
	 * @throws \yii\base\Exception
	 */
	public function onException (ExceptionEvent $event)
	{
		$exception = $event->exception;

		if (!($exception instanceof HttpException) || $exception->statusCode !== 404)
			return;

		$path = \Craft::$app->request->getFullPath();
		$query = \Craft::$app->request->getQueryStringWithoutPath();

		if ($query)
			$path .= '?' . $query;

		if ($redirect = $this->findRedirectByPath($path))
		{
			$event->handled = true;
			\Craft::$app->response->redirect($redirect['to'], $redirect['type']);
		}
	}

	// Finders
	// -------------------------------------------------------------------------

	/**
	 * Returns all the redirects
	 *
	 * @return array|\yii\db\ActiveRecord[]
	 */
	public function findAllRedirects ()
	{
		return RedirectRecord::find()->all();
	}

	/**
	 * Finds a redirect matching the given path
	 *
	 * @param $path
	 *
	 * @return array|bool
	 * @throws \yii\base\Exception
	 */
	public function findRedirectByPath ($path)
	{
		$redirects = $this->findAllRedirects();

		foreach ($redirects as $redirect)
		{
			$to = false;

			if (trim($redirect['uri'], '/') == $path)
			{
				$to = $redirect['to'];
			}
			elseif ($uri = $this->_isRedirectRegex($redirect['uri']))
			{
				if (preg_match($uri, $path))
					$to = preg_replace($uri, $redirect['to'], $path);
			}

			if ($to)
			{
				return [
					'to' => strpos($to, '://') !== false
						? $to
						: UrlHelper::siteUrl($to),
					'type' => $redirect['type'],
				];
			}
		}

		return false;
	}

	// Save / Update / Delete
	// -------------------------------------------------------------------------

	/**
	 * Saves the redirect
	 *
	 * @param string   $uri
	 * @param string   $to
	 * @param string   $type
	 * @param int|null $id
	 *
	 * @return array|int|string
	 */
	public function save ($uri, $to, $type, $id = null)
	{
		if ($id)
		{
			$record = RedirectRecord::findOne(compact('id'));

			if (!$record)
				return 'Unable to find redirect with ID: ' . $id;
		}
		else
		{
			$doesUriExist = RedirectRecord::findOne(compact('uri'));

			if ($doesUriExist)
				return 'A redirect with that URI already exists!';

			$record = new RedirectRecord();
		}

		$record->uri   = $uri;
		$record->to    = $to;
		$record->type  = $type;

		if (!$record->save())
			return $record->getErrors();

		return $record->id;
	}

	/**
	 * Deletes the redirect with the given ID
	 *
	 * @param int $id
	 *
	 * @return bool|string
	 * @throws \Exception
	 * @throws \Throwable
	 * @throws \yii\db\StaleObjectException
	 */
	public function delete ($id)
	{
		$redirect = RedirectRecord::findOne(compact('id'))->delete();

		if ($redirect === false)
			return 'Unable find redirect with ID: ' . $id;

		return false;
	}

	// Private Methods
	// =========================================================================

	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Checks if the redirect URI is a regex
	 *
	 * @param string $uri
	 *
	 * @return bool|mixed|string
	 */
	private function _isRedirectRegex ($uri)
	{
		// Escape all non-escaped `?` not inside parentheses
		$i = preg_match_all(
			'/(?<!\\\\)\?(?![^(]*\))/',
			$uri,
			$matches,
			PREG_OFFSET_CAPTURE
		);

		while ($i--)
		{
			$x = $matches[0][$i][1];
			$uri = substr_replace($uri, '\?', $x, 1);
		}

		// Check if contains a regex
		if (preg_match('/^#(.+)#$/', $uri))
		{
			return $uri;
		}
		elseif (strpos($uri, '*'))
		{
			return '#^' . str_replace(['*','/'], ['(.*)', '\/'], $uri) . '#';
		}

		return false;
	}

}