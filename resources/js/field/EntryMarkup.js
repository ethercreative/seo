/* globals $, Craft, Garnish */
/**
 * Entry Markup
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import { c, fail } from "../helpers";

class EntryMarkup {
	
	// Variables
	// =========================================================================
	
	frame = null;
	postData = null;
	token = null;
	
	// Entry Markup
	// =========================================================================
	
	constructor () {
		this.clean();
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Gets and stores a parse-able preview of the entry markup
	 *
	 * @return {Promise}
	 */
	update (SEO) {
		return new Promise(async (resolve, reject) => {
			const nextPostData = Garnish.getPostData(
				document.getElementById("main-form")
			);
			
			// Skip if no changes have been made to the content
			if (this.postData && Craft.compare(nextPostData, this.postData)) {
				resolve(this.frame.contentWindow.document.body);
				return;
			}
			
			this.postData = nextPostData;

			try {
				// Ensure we have a preview token
				await this._getToken(SEO);

				// Get the markup from the live preview
				let data = await this._preview(nextPostData);

				// Remove all <script/> & <style/> tags
				data = data.replace(
					/<script([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/script>/g,
					""
				);

				data = data.replace(
					/<style([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/style>/g,
					""
				);

				// Write the markup to our iFrame
				this.frame.contentWindow.document.open();
				this.frame.contentWindow.document.write(data);
				this.frame.contentWindow.document.close();

				if (this.frame.contentWindow.document.body) {
					resolve(this.frame.contentWindow.document.body);
				} else {
					// noinspection ExceptionCaughtLocallyJS
					throw null;
				}
			} catch (_) {
				fail('Failed to retrieve entry preview');
				reject();
			}
		});
	}
	
	/**
	 * Creates an empty, hidden iframe to store the preview content, removing
	 * the old one (if it exists)
	 */
	clean () {
		this.frame && document.body.removeChild(this.frame);
		
		this.frame = c("iframe", {
			frameborder: "0",
			style: `
				width: 0;
				height: 0;
			`
		});
		
		document.body.appendChild(this.frame);
	}

	// Helpers
	// =========================================================================

	_preview (nextPostData) {
		return new Promise(((resolve, reject) => {
			$.ajax({
				url: Craft.livePreview.previewUrl,
				data: $.extend({}, nextPostData, Craft.livePreview.basePostData),
				method: 'POST',
				headers: { 'X-Craft-Token': this.token },
				xhrFields: { withCredentials: true },
				crossDomain: true,
				success: resolve,
				error: reject,
			});
		}));
	}

	_getToken (SEO) {
		return new Promise((resolve, reject) => {
			if (this.token !== null)
				return resolve();

			Craft.postActionRequest('live-preview/create-token', {
				previewAction: SEO.options.previewAction
			}, (response, textStatus) => {
				if (textStatus === 'success') {
					this.token = response.token;
					resolve();
				} else {
					reject();
				}
			});
		});
	}
	
}

export default new EntryMarkup();
