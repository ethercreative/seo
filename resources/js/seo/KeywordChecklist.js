/**
 * Keyword Checklist
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import EntryMarkup from "./EntryMarkup";
import { SEO_RATING, SEO_REASONS } from "../const";
import {
	countInArray, createRating, isExternalUrl, t,
	TextStatistics
} from "../helpers";

export default class KeywordChecklist {
	
	// Variables
	// =========================================================================
	
	keyword = null;
	keywordLower = null;
	ratings = [];
	ratingOccurrence = {};
	
	content = null;
	text = null;
	stats = null;
	
	// KeywordChecklist
	// =========================================================================
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;
		
		this.bar = document.getElementById(`${namespace}KeywordBar`);
		this.checklist = document.getElementById(`${namespace}KeywordChecklist`);
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Calculates the checklist ratings for the given keyword
	 *
	 * @param {string} keyword
	 * @param {Function} onNewRating
	 */
	calculate (keyword, onNewRating) {
		EntryMarkup.update().then(content => {
			this.ratings = [];
			this.keyword = keyword;
			this.keywordLower = keyword.toLowerCase();
			
			// Remove line breaks, tabs, and surplus spaces from page text
			this.text = content.textContent.replace(
				/(\r\n|\r|\n|\t|\s+)/gmi,
				""
			);
			
			// If there's no text, complain
			if (this.text.trim() === "") {
				this.addRating(
					SEO_RATING.POOR,
					SEO_REASONS.noContent
				);
				onNewRating(SEO_RATING.POOR);
				return;
			}
			
			this.content = content;
			this.stats = new TextStatistics(this.text);
			
			// Run all `judge` functions
			Object.getOwnPropertyNames(KeywordChecklist.prototype)
			      .filter(f => f.indexOf("judge") > -1)
			      .forEach(f => { this[f](); });

			// Sort the results by rating, keeping track of the number of times
			// each rating occurs
			this.ratingOccurrence = {};
			
			this.ratings.sort((a, b) => {
				return (
					KeywordChecklist.ratingValue(b.rating)
					- KeywordChecklist.ratingValue(a.rating)
				);
			}).forEach(({ rating }) => {
				if (!this.ratingOccurrence.hasOwnProperty(rating))
					this.ratingOccurrence[rating] = 0;
				
				this.ratingOccurrence[rating]++;
			});
			
			// Find the most prevalent rating
			const overallRating =
				Object.keys(this.ratingOccurrence)
				      .reduce(
				      	(a, b) =>
					        this.ratingOccurrence[a] > this.ratingOccurrence[b]
						        ? a : b
				      );
			
			// Re-render the checklist
			this.renderChecklist();
			
			// Run the callback
			onNewRating(overallRating);
		}).catch(err => {
			console.log(err);
			// TODO: Disable checklist, show error overlaying
			// Note to self: This also catches JS errors
		});
	}
	
	/**
	 * Renders the checklist & bar
	 */
	renderChecklist () {
		// Re-render bar
		const ratingCount = this.ratings.length;
		let currentFillSize = 0;
		for (let i = 0; i < this.bar.children.length; i++) {
			let fill = this.bar.children[i];
			let rating = fill.className;
			
			fill.style.transform = `translateX(${currentFillSize}%)`;
			
			if (this.ratingOccurrence.hasOwnProperty(rating)) {
				currentFillSize +=
					(this.ratingOccurrence[rating] / ratingCount) * 100;
			}
		}
		
		// Re-render checklist
		while (this.checklist.firstElementChild)
			this.checklist.removeChild(this.checklist.firstElementChild);
		
		this.ratings.forEach(rating => {
			this.checklist.appendChild(this.renderChecklistItem(rating));
		});
	}
	
	// Calculations
	// =========================================================================
	
	/**
	 * Judge the length of the title
	 */
	judgeTitleLength () {
		const l = this.SEO.snippetFields.title.value.length;
		
		this.addRating(
			l < 40 || l > 60 ? SEO_RATING.POOR : SEO_RATING.GOOD,
			l < 40
				? SEO_REASONS.titleLengthFailMin.replace("{l}", l)
				: l > 60
					? SEO_REASONS.titleLengthFailMax.replace("{l}", l)
					: SEO_REASONS.titleLengthSuccess
		);
	}
	
	/**
	 * Judge the positioning of the keyword in the title
	 */
	judgeTitleKeyword () {
		const title = this.SEO.snippetFields.title.value;
		const index = title.toLowerCase().indexOf(this.keywordLower);
		
		if (index > -1) {
			if (index <= title.length * 0.3) {
				this.addRating(
					SEO_RATING.GOOD,
					SEO_REASONS.titleKeywordSuccess
				);
				return;
			}
			
			this.addRating(
				SEO_RATING.AVERAGE,
				SEO_REASONS.titleKeywordPosFail
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.titleKeywordFail
		);
	}
	
	/**
	 * Judge the keyword in the slug
	 */
	judgeSlug () {
		if (!this.SEO.snippetFields.slug)
			return;
		
		const slug = this.SEO.snippetFields.slug.textContent.toLowerCase();
		const keyword = this.keywordLower.replace(/[^a-zA-Z0-9-_]/g, "");
		
		if (slug.indexOf(keyword) > -1) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.slugSuccess
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.slugFail
		);
	}
	
	/**
	 * Judge the location of the keyword in the description
	 *
	 * TODO: Check if keyword appears in first half / appearance count
	 */
	judgeDesc () {
		const desc = this.SEO.snippetFields.desc.value.toLowerCase();
		
		if (desc.indexOf(this.keywordLower) > -1) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.descSuccess
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.descFail
		);
	}
	
	/**
	 * Judge the number of words
	 */
	judgeWordCount () {
		const count = this.stats.wordCount();
		
		if (count > 300) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.wordCountSuccess.replace("{l}", count)
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.wordCountFail.replace("{l}", count)
		);
	}
	
	/**
	 * Judge keyword in first paragraph
	 */
	judgeFirstParagraph () {
		const p = this.content.querySelector("p");
		
		if (p && p.textContent.toLowerCase().indexOf(this.keywordLower) > -1) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.firstParagraphSuccess
			);
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.firstParagraphFail
		);
	}
	
	/**
	 * Judge the number of images with the keyword in their alts
	 *
	 * TODO: Look into <picture> & <figure> tag effect on SEO
	 */
	judgeImages () {
		const imgs = this.content.getElementsByTagName("img");
		
		if (!imgs.length) return;
		
		let withKeywordAlt = 0,
			i = imgs.length;
		
		while (i--) {
			let alt = imgs[i].getAttribute("alt");
			if (alt && alt.toLowerCase().indexOf(this.keywordLower) > -1)
				withKeywordAlt++;
		}
		
		if (withKeywordAlt >= imgs.length * 0.8) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.imagesSuccess
			);
			return;
		}
		
		if (withKeywordAlt >= imgs.length * 0.5) {
			this.addRating(
				SEO_RATING.AVERAGE,
				SEO_REASONS.imagesOk
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.imagesFail
		);
	}
	
	/**
	 * Judge whether the content contains external links
	 *
	 * TODO: Should this be counting instead just seeing if one exists?
	 */
	judgeLinks () {
		const a = this.content.getElementsByTagName("a");
		
		if (!a.length) return;
		
		for (let i = 0; i < a.length; i++) if (isExternalUrl(a[i].href)) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.linksSuccess
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.linksFail
		);
	}
	
	/**
	 * Judge the number of headings (and their level) that contain the keyword
	 */
	judgeHeadings () {
		const headings = this.content.querySelectorAll("h1, h2, h3, h4, h5, h6");
		
		if (!headings.length) return;
		
		let primary = 0,
			secondary = 0,
			i = headings.length;
		
		while (i--) {
			let h = headings[i];
			
			if (h.textContent.toLowerCase().indexOf(this.keywordLower) === -1)
				continue;
			
			if (["h1", "h2"].indexOf(h.nodeName.toLowerCase()) > -1) primary++;
			else secondary++;
		}
		
		if (primary > 0) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.headingsSuccess
			);
			return;
		}
		
		if (secondary > 0) {
			this.addRating(
				SEO_RATING.AVERAGE,
				SEO_REASONS.headingsOk
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.headingsFail
		);
	}
	
	/**
	 * Judge the density of the keyword within the content
	 */
	judgeDensity () {
		const words = this.stats.words();
		const keyCount = countInArray(words, this.keywordLower);
		const keyPercent = +(
			100 + (
				(keyCount - words.length) / words.length
			) * 100
		).toFixed(2);
		
		if (keyPercent < 1.0) {
			this.addRating(
				SEO_RATING.POOR,
				SEO_REASONS.densityFailUnder.replace("{d}", keyPercent)
			);
			return;
		}
		
		if (keyPercent <= 2.5) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.densitySuccess.replace("{d}", keyPercent)
			);
			return;
		}
		
		if (keyPercent > 2.5) {
			this.addRating(
				SEO_RATING.AVERAGE,
				SEO_REASONS.densityOk
				           .replace("{d}", keyPercent)
				           .replace("{c}", keyCount)
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.densityFail
		);
	}
	
	/**
	 * Judges the Flesch-Kincaid reading ease
	 */
	judgeFleschEase () {
		const level = this.stats.fleschKincaidReadingEase();
		
		if (level >= 80) {
			this.addRating(
				SEO_RATING.GOOD,
				SEO_REASONS.fleschSuccess.replace("{l}", level)
			);
			return;
		}
		
		if (level >= 60) {
			this.addRating(
				SEO_RATING.AVERAGE,
				SEO_REASONS.fleschOk.replace("{l}", level)
			);
			return;
		}
		
		this.addRating(
			SEO_RATING.POOR,
			SEO_REASONS.fleschFail.replace("{l}", level)
		);
		return;
	}
	
	// Helpers
	// =========================================================================
	
	/**
	 * Converts the SEO Rating to a number (for sorting)
	 *
	 * @param {string} rating
	 * @return {number}
	 */
	static ratingValue (rating) {
		switch (rating) {
			case SEO_RATING.POOR:
				return -1;
			case SEO_RATING.AVERAGE:
				return 1;
			case SEO_RATING.GOOD:
				return 2;
			default:
				return 0;
		}
	}
	
	/**
	 * Adds a rating
	 *
	 * @param {string} rating
	 * @param {string} reason
	 */
	addRating (rating, reason) {
		this.ratings.push({ rating, reason });
	}
	
	/**
	 * Renders a checklist item
	 *
	 * @param {string} rating
	 * @param {string} reason
	 * @return {Element}
	 */
	renderChecklistItem = ({ rating, reason }) => {
		return t("li", {}, [
			createRating(rating),
			t("p", {}, reason)
		]);
	};
	
}

