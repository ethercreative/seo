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
	 * @return void
	 * @throws \yii\base\Exception
	 * @throws \yii\base\ExitException
	 */
	public function onException (ExceptionEvent $event)
	{
		$exception = $event->exception;
		$craft = \Craft::$app;

		if (!($exception instanceof HttpException) || $exception->statusCode !== 404)
			return;

		$path = $craft->request->getFullPath();
		$query = $craft->request->getQueryStringWithoutPath();

		if ($query)
			$path .= '?' . $query;

		if ($redirect = $this->findRedirectByPath($path))
		{
			$event->handled = true;
			$craft->response->redirect($redirect['to'], $redirect['type'])
			                ->send();
			$craft->end();
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
	 * Bulk creates redirects
	 *
	 * @param $redirects
	 * @param $separator
	 * @param $type
	 *
	 * @return array
	 */
	public function bulk ($redirects, $separator, $type)
	{
		$rawRedirects = array_map(function ($line) use ($separator) {
			return str_getcsv($line, $separator);
		}, explode(PHP_EOL, $redirects));

		$newFormatted = [];

		foreach ($rawRedirects as $redirect)
		{
			$record = new RedirectRecord();
			$record->uri  = $redirect[0];
			$record->to   = $redirect[1];
			$record->type = array_key_exists(2, $redirect) ? $redirect[2] : $type;
			$record->save();

			$newFormatted[] = [
				'id'   => $record->id,
				'uri'  => $record->uri,
				'to'   => $record->to,
				'type' => $record->type,
			];
		}

		return [$newFormatted, false];
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