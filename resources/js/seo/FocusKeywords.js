/**
 * Focus Keywords
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     1.5.0
 */

import { TextStatistics, fail, t, capitalize } from "../helpers";

const SEO_RATING = {
	NONE: "neutral",
	GOOD: "good",
	AVERAGE: "average",
	POOR: "poor",
};

// TODO: Make translatable
const SEO_REASONS = {
	titleLengthFailMin: "The title contains {l} characters which is less than the recommended minimum of 40 characters.",
	titleLengthFailMax: "The title contains {l} characters which is greater than the recommended maximum of 60 characters.",
	titleLengthSuccess: "The title is between the recommended minimum and maximum length.",
	
	titleKeywordFail: "The title does not contain the keyword. Try adding it near the beginning of the title.",
	titleKeywordSuccess: "The title contains the keyword near the beginning.",
	titleKeywordPosFail: "The title contains the keyword, but not near the beginning. Try to move it closer to the start of the title.",
	
	slugFail: "The URL does not contain the keyword. Try adding it to the slug.",
	slugSuccess: "The URL contains the keyword.",
	
	descFail: "The description does not contain the keyword. Try adding it near the beginning of the description.",
	descSuccess: "The description contains the keyword.",
	
	wordCountFail: "Your text contains {l} words, this is less than the recommended 300 word minimum.",
	wordCountSuccess: "Your text contains {l} words, this is more than the recommended 300 word minimum.",
	
	firstParagraphFail: "The keyword does not appear in the first paragraph of your text. Try adding it.",
	firstParagraphSuccess: "The keyword appears in the first paragraph of your text.",
	
	imagesFail: "Less than half of the images have alt tags containing the keyword, try adding it to more images.",
	imagesOk: "Half or more of the images have alt tags containing the keyword. To improve this, try adding keywords to all the images alt tags.",
	imagesSuccess: "All of the images have alt tags containing the keyword.",
	
	linksFail: "The page does not contain any outgoing links. Try adding some.",
	linksSuccess: "The page contains outgoing links.",
	
	headingsFail: "The page does not contain any headings that contain the keyword. Try adding some with the keyword.",
	headingsOk: "The page contains some lower importance headings that contain the keyword. Try adding the keyword to some h2's.",
	headingsSuccess: "The page contains higher importance headings with the keyword.",
	
	densityFail: "The keyword does not appear in the text. It is important to include it in your content.",
	densityFailUnder: "The keyword density is {d}%, which is well under the advised 2.5%. Try increasing the number of times the keyword is used.",
	densityOk: "The keyword density is {d}%, which is over the advised 2.5%. The keyword appears {c} times.",
	densitySuccess: "The keyword density is {d}%, which is near the advised 2.5%.",
	
	fleschFail: "The Flesch Reading ease score is {l} which is considered best for university graduates. Try reducing your sentence length to improve readability.",
	fleschOk: "The Flesch Reading ease score is {l} which is average, and considered easily readable by most users.",
	fleschSuccess: "The Flesch Reading ease score is {l} meaning your content is readable by all ages.",
};

export default class FocusKeywords {
	
	activeKeywordIndex = null;
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;
		
		this.keywordsField = document.getElementById(`${namespace}Keywords`);
		this.scoreField = document.getElementById(`${namespace}Score`);
		
		this.initInput();
		this.initKeywords();
	}
	
	// Initializers
	// =========================================================================
	
	/**
	 * Initializes the keywords input
	 */
	initInput () {
		// Variables
		this.inputWrap =
			document.getElementById(this.namespace + "KeywordsInput");
		this.input = this.inputWrap.lastElementChild;
		
		// Events
		this.inputWrap.addEventListener("click", this.onInputWrapClick);
		this.input.addEventListener("focus", this.onInputFocus);
		this.input.addEventListener("blur", this.onInputBlur);
		this.input.addEventListener("keydown", this.onInputKeyDown);
	}
	
	/**
	 * Initializes the keywords (if any exist)
	 */
	initKeywords () {
		// Set initial keywords, adding the index variable
		this.keywords = JSON.parse(this.keywordsField.value).map((k, i) => ({
			...k,
			index: i,
		}));
		
		// Initial keywords
		this.keywords.forEach(({ keyword, score }, i) => {
			this.createKeyword(keyword, score, i);
		});
		
		// Set the first keyword (if we have one) to be active
		this.keywords.length && this.setActiveKeyword(0);
		
		this.onKeywordsChange();
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Sets the active keyword & triggers an update to the keyword details
	 *
	 * @param index
	 */
	setActiveKeyword (index) {
		if (
			this.activeKeywordIndex !== null
		    && this.keywords.length > this.activeKeywordIndex
		) {
			this.getKeywordElementAtIndex(this.activeKeywordIndex)
			    .classList.remove("active");
		}
		
		if (this.keywords.length > index) {
			this.activeKeywordIndex = index|0;
			this.getKeywordElementAtIndex(this.activeKeywordIndex)
			    .classList.add("active");
		}
		
		// TODO: Trigger results update
	}
	
	// Events
	// =========================================================================
	
	/**
	 * Update the hidden inputs when the keywords change
	 */
	onKeywordsChange = () => {
		const ratings = Object.values(SEO_RATING).reduce((a, b) => {
			a[b] = 0;
			return a;
		}, {});
		
		this.keywordsField.value = JSON.stringify(
			this.keywords.map(({ keyword, score }) => {
				ratings[score]++;
				return { keyword, score };
			})
		);
		
		this.scoreField.value = Object.keys(ratings).reduce((a, b) => {
			return ratings[b] > a.score ? { rating: b, score: ratings[b] } : a;
		}, { rating: SEO_RATING.NONE, score: 0 }).rating;
	};
	
	// Events: Keywords
	// -------------------------------------------------------------------------
	
	/**
	 * Fixed when a keyword element is clicked.
	 * Will set that keyword to be the active one.
	 *
	 * @param {Event} e
	 */
	onKeywordClick = e => {
		e.preventDefault();
		this.setActiveKeyword(e.target.dataset.index);
	};
	
	/**
	 * Fired when the [x] in the keyword element is clicked.
	 * Will remove the element and the keyword from `this.keywords`.
	 *
	 * @param {Event} e
	 */
	onKeywordRemoveClick = e => {
		e.preventDefault();
		e.stopPropagation();
		
		const i = e.target.parentNode.parentNode.dataset.index|0;
		
		this.activeKeywordIndex === i && this.setActiveKeyword(0);
		
		// Remove keyword
		const elem = this.getKeywordElementAtIndex(i);
		elem.parentNode.removeChild(elem);
		
		this.keywords.splice(i, 1);
		
		// Re-map indexes
		this.keywords = this.keywords.map((k, i) => {
			if (this.activeKeywordIndex === k.index)
				this.activeKeywordIndex = i;
			
			this.getKeywordElementAtIndex(i)
			    .setAttribute("data-index", i);
			
			return {
				...k,
				index: i,
			};
		});
		
		this.onKeywordsChange();
	};
	
	// Events: Keywords Input
	// -------------------------------------------------------------------------
	
	/**
	 * Fired when the keywords input wrapper is clicked
	 *
	 * @param {Event} e
	 */
	onInputWrapClick = e => {
		if (e.target === this.inputWrap)
			this.input.focus();
	};
	
	/**
	 * Fired when the keywords input is focused
	 */
	onInputFocus = () => {
		this.inputWrap.classList.add("focused");
	};
	
	/**
	 * Fired when the keywords input is blurred
	 */
	onInputBlur = () => {
		this.inputWrap.classList.remove("focused");
	};
	
	/**
	 * Fired when a key is pressed while the keywords input is focused
	 *
	 * @param {Event} e
	 */
	onInputKeyDown = e => {
		if (e.keyCode !== 13) return;
		e.preventDefault();
		
		const nextKeyword = e.target.value.trim();
		let dupe = false;
		
		if (!nextKeyword) return;
		
		// Check if this is a duplicate and activate original if it is
		this.keywords.forEach(({ keyword, index }) => {
			if (nextKeyword === keyword) {
				dupe = true;
				this.setActiveKeyword(index);
			}
		});
		
		// If it's not a duplicate, create a new keyword
		!dupe && this.createKeyword(nextKeyword);
		
		// Reset the input
		e.target.value = "";
	};
	
	// Helpers
	// =========================================================================
	
	/**
	 * Creates a rating element
	 *
	 * @param {string} level
	 * @param {string=} tag
	 * @return {Element}
	 */
	createRating = (level, tag = "div") => {
		const name = capitalize(level);
		
		return t(tag, {
			"class": `seo--light ${level}`,
			"title": name,
		}, name);
	};
	
	/**
	 * Creates a new keyword and adds it to the keyword input
	 *
	 * @param {string} keyword
	 * @param {string=} score
	 * @param {number|null=} index
	 */
	createKeyword = (keyword, score = "neutral", index = null) => {
		const nextIndex = index !== null ? index : this.keywords.length;
		
		const elem = t("a", {
			href: "#",
			click: this.onKeywordClick,
			"data-index": nextIndex,
		}, [
			this.createRating(score, "span"),
			keyword,
			t("object", {}, t("a", {
				href: "#",
				title: "Remove",
				click: this.onKeywordRemoveClick,
			}, "Remove"))
		]);
		
		this.inputWrap.insertBefore(
			elem,
			this.inputWrap.lastElementChild
		);
		
		if (index === null) {
			this.keywords.push({
				keyword,
				score,
				index: nextIndex,
			});
			
			this.setActiveKeyword(nextIndex);
			this.onKeywordsChange();
		}
	};
	
	/**
	 * Gets a keyword element at a given index
	 *
	 * @param {number} index
	 * @return {HTMLElement}
	 */
	getKeywordElementAtIndex = (index) => this.inputWrap.children[index];
	
}