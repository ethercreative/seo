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

import { t, fail } from "../helpers";

class EntryMarkup {
	
	// Variables
	// =========================================================================
	
	frame = null;
	postData = null;
	
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
		return new Promise((resolve, reject) => {
			const nextPostData = Garnish.getPostData(
				document.getElementById("container")
			);
			
			if (this.postData && Craft.compare(nextPostData, this.postData)) {
				resolve(this.frame.contentWindow.document.body);
				return;
			}
			
			this.postData = nextPostData;
			
			$.ajax({
				url: Craft.livePreview.previewUrl,
				method: "POST",
				data: $.extend({}, nextPostData, Craft.livePreview.basePostData),
				xhrFields: { withCredentials: true },
				crossDomain: true,
				success: data => {
					// TODO: Remove all `autoplay` attributes
					data = data.replace(
						/<script([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/script>/g,
						""
					);
					
					data = data.replace(
						/<style([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/style>/g,
						""
					);
					
					this.frame.contentWindow.document.open();
					this.frame.contentWindow.document.write(data);
					this.frame.contentWindow.document.close();
					
					if (this.frame.contentWindow.document.body) {
						resolve(this.frame.contentWindow.document.body);
					} else {
						fail("Failed to parse entry preview");
						reject();
					}
				},
				error: () => {
					fail("Failed to retrieve entry preview");
					reject();
				}
			});
		});
	}
	
	/**
	 * Creates an empty, hidden iframe to store the preview content, removing
	 * the old one (if it exists)
	 */
	clean () {
		this.frame && document.body.removeChild(this.frame);
		
		this.frame = t("iframe", {
			frameborder: "0",
			style: `
				width: 0;
				height: 0;
			`
		});
		
		document.body.appendChild(this.frame);
	}
	
}

export default new EntryMarkup();