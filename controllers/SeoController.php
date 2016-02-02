<?php

namespace Craft;

class SeoController extends BaseController
{

	/**
	 * Outputs fields to HTML (based off of EntriesController::actionPreviewEntry())
	 *
	 * @return mixed
	 * @throws HttpException
	 */
	public function actionParser ()
	{
		// To stop Craft overriding error handling
		if (craft()->config->get('devMode')) error_reporting(0);

		$versionId = craft()->request->getPost('versionId');

		if ($versionId)
		{
			$entry = craft()->entryRevisions->getVersionById($versionId);

			if (!$entry)
			{
				throw new HttpException (404);
			}
		}
		else
		{
			$entry = $this->_getEntryModel();
			craft()->setLanguage(craft()->getTargetLanguage(true));
			$this->_populateEntryModel($entry);
		}

		if (!$entry->postDate)
		{
			$entry->postDate = new DateTime();
		}

		$this->_showEntry($entry);
	}

	/**
	 * @return EntryModel
	 * @throws Exception
	 */
	private function _getEntryModel ()
	{
		$entryId = craft()->request->getPost('entryId');
		$localeId = craft()->request->getPost('locale');

		if ($entryId)
		{
			$entry = craft()->entries->getEntryById($entryId, $localeId);

			if (!$entry)
			{
				throw new Exception(Craft::t('No entry exists with the ID “{id}”.', array('id' => $entryId)));
			}
		}
		else
		{
			$entry = new EntryModel();
			$entry->sectionId = craft()->request->getRequiredPost('sectionId');

			if ($localeId)
			{
				$entry->locale = $localeId;
			}
		}

		return $entry;
	}

	/**
	 * @param EntryModel $entry
	 */
	private function _populateEntryModel (EntryModel $entry)
	{
		// Set the entry attributes, defaulting to the existing values for whatever is missing from the post data
		$entry->typeId        = craft()->request->getPost('typeId', $entry->typeId);
		$entry->slug          = craft()->request->getPost('slug', $entry->slug);
		$entry->postDate      = (($postDate   = craft()->request->getPost('postDate'))   ? DateTime::createFromString($postDate,   craft()->timezone) : $entry->postDate);
		$entry->expiryDate    = (($expiryDate = craft()->request->getPost('expiryDate')) ? DateTime::createFromString($expiryDate, craft()->timezone) : null);
		$entry->enabled       = (bool) craft()->request->getPost('enabled', $entry->enabled);
		$entry->localeEnabled = (bool) craft()->request->getPost('localeEnabled', $entry->localeEnabled);

		$entry->getContent()->title = craft()->request->getPost('title', $entry->title);

		$fieldsLocation = craft()->request->getParam('fieldsLocation', 'fields');
		$entry->setContentFromPost($fieldsLocation);

		// Author
		$authorId = craft()->request->getPost('author', ($entry->authorId ? $entry->authorId : craft()->userSession->getUser()->id));

		if (is_array($authorId))
		{
			$authorId = isset($authorId[0]) ? $authorId[0] : null;
		}

		$entry->authorId = $authorId;

		// Parent
		$parentId = craft()->request->getPost('parentId');

		if (is_array($parentId))
		{
			$parentId = isset($parentId[0]) ? $parentId[0] : null;
		}

		$entry->parentId = $parentId;

		// Revision notes
		$entry->revisionNotes = craft()->request->getPost('revisionNotes');
	}

	/**
	 * @param EntryModel $entry
	 * @return mixed
	 * @throws HttpException
	 */
	private function _showEntry (EntryModel $entry) {
		craft()->elements->setPlaceholderElement($entry);

		craft()->templates->getTwig()->disableStrictVariables();

		$fieldTemplates = craft()->plugins->getPlugin('seo')->getSettings()->fieldTemplates;
		$fields = explode(',', craft()->request->getRequiredParam('fields'));
		$template = '';

		foreach ($fields as $field) {
			if ($fieldTemplates !== null && $fieldTemplates[$field]) {
				$fieldTemplate = $fieldTemplates[$field];
			} else {
				$fieldTemplate = "{{ entry.{$field} }}";
			}

			$template .= "<seo-parse data-field='{$field}'>{$fieldTemplate}</seo-parse> "; // Space at end is important!
		}

		$output = $this->renderTemplate(new StringTemplate(md5(time()), $template), array(
			'entry' => $entry
		), true);

		HeaderHelper::setContentTypeByExtension('html');
		HeaderHelper::setHeader(array('charset' => 'utf-8'));
		ob_start();
		echo strip_tags($output, '<p><h1><h2><h3><h4><h5><h6><a><img><b><strong><seo-parse>');
		craft()->end();
	}

}