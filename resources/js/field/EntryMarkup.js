/* globals $, Craft, Garnish */
/* eslint-disable no-async-promise-executor */
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
	update () {
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
				// Get the markup from the live preview
				let data = await this._preview();

				// Remove all <svg/>, <script/> & <style/> tags
				const svgTags = data.match(/<svg([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/svg>/g);
				if (svgTags) {
					svgTags.forEach(s => {
						if (typeof s !== 'string') return;
						const t = s.match(/<text([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/text>/g) || [];
						data = data.replace(s, '<svg>' + t.join() + '</svg>');
					});
				}

				data = data.replace(
					/<script([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/script>/g,
					''
				);

				data = data.replace(
					/<style([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/style>/g,
					''
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
			} catch (e) {
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

	_preview () {
		return new Promise((async (resolve, reject) => {
			const elementEditor = window.Craft.cp.$primaryForm.data('elementEditor');

			if (elementEditor.settings.previewTargets.length === 0)
				reject();

			$.ajax({
				url: await elementEditor.getTokenizedPreviewUrl(elementEditor.settings.previewTargets[0].url),
				// data: $.extend({}, nextPostData, Craft.livePreview.basePostData),
				method: 'GET',
				// headers: { 'X-Craft-Token': this.token },
				xhrFields: { withCredentials: true },
				crossDomain: true,
				success: resolve,
				error: reject,
			});
		}));
	}
	
}

export default new EntryMarkup();
