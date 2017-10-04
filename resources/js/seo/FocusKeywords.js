/**
 * Focus Keywords
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import { t, createRating, debounce } from "../helpers";

import KeywordChecklist from "./KeywordChecklist";
import { SEO_RATING_LABEL } from "../const";

export default class FocusKeywords {
	
	activeKeywordIndex = null;
	
	constructor (namespace, SEO) {
		this.namespace = namespace;
		this.SEO = SEO;
		this.keywordsChecklist = new KeywordChecklist(namespace, SEO);
		
		this.keywordsField = document.getElementById(`${namespace}Keywords`);
		this.scoreField = document.getElementById(`${namespace}Score`);
		
		this.keywordElem = document.getElementById(`${namespace}Keyword`);
		this.ratingElem = document.getElementById(`${namespace}Rating`);
		
		this.initInput();
		this.initKeywords();
		
		this.initWatcher();
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
		this.keywords.forEach(({ keyword, rating }, i) => {
			this.createKeyword(keyword, rating, i);
		});
		
		// Set the first keyword (if we have one) to be active
		this.keywords.length && this.setActiveKeyword(0);
		
		this.onKeywordsChange();
	}
	
	/**
	 * Watches for any changes in the form containing the SEO field, triggering
	 * an update when a change occurs
	 */
	initWatcher () {
		this.mo = new MutationObserver(debounce(this.recalculateKeyword, 500));
		
		this.startObserving();
	}
	
	// Actions
	// =========================================================================
	
	/**
	 * Start watching the form
	 */
	startObserving () {
		if (!this.mo) return;
		
		this.mo.observe(this.keywordsField.form, {
			childList: true,
			attributes: true,
			characterData: true,
			subtree: true,
		});
	}
	
	/**
	 * Stop watching the form
	 */
	stopObserving () {
		if (!this.mo) return;
		
		this.mo.disconnect();
		this.mo.takeRecords();
	}
	
	/**
	 * Sets the active keyword & triggers an update to the keyword details
	 *
	 * @param index
	 */
	setActiveKeyword (index) {
		if (!this.keywords.length > index) this.activeKeywordIndex = null;
		if (this.activeKeywordIndex === index) return;
		
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
			
			// Re-calculate
			this.recalculateKeyword();
		} else {
			this.activeKeywordIndex = null;
		}
	}
	
	/**
	 * Re-calculate the checklist & rating
	 */
	recalculateKeyword = () => {
		// Stop watching the form to prevent an update loop
		this.stopObserving();
		
		if (this.activeKeywordIndex !== null) {
			const keyword = this.keywords[this.activeKeywordIndex];
			
			this.keywordsChecklist.calculate(
				keyword.keyword,
				this.onNewRating.bind(this, keyword.index)
			);
		}
		
		setTimeout(() => {
			// Start watching the form again, now updates are complete
			this.startObserving();
		}, 1);
	};
	
	// Events
	// =========================================================================
	
	/**
	 * Update the hidden inputs when the keywords change
	 */
	onKeywordsChange = () => {
		const ratingOccurrence = {};
		
		this.keywordsField.value = JSON.stringify(
			this.keywords.map(({ keyword, rating }) => {
				if (!ratingOccurrence.hasOwnProperty(rating))
					ratingOccurrence[rating] = 0;
				
				ratingOccurrence[rating]++;
				
				return { keyword, rating };
			})
		);
		
		if (!Object.keys(ratingOccurrence).length) {
			this.scoreField.value = "";
			return;
		}
		
		this.scoreField.value =
			Object.keys(ratingOccurrence)
			      .reduce(
				      (a, b) =>
					      ratingOccurrence[a] > ratingOccurrence[b] ? a : b
			      );
	};
	
	/**
	 * Fired when the keyword checklist generates a new keyword rating
	 *
	 * @param {number} keywordIndex
	 * @param {string} rating
	 */
	onNewRating = (keywordIndex, rating) => {
		const keyword = this.keywords[keywordIndex];
		keyword.rating = rating;
		
		// TODO: Re-render keyword rating in input
		
		// Set keyword details keyword
		this.keywordElem.textContent = keyword.keyword;
		
		// Set keyword details rating
		while (this.ratingElem.firstChild)
			this.ratingElem.removeChild(this.ratingElem.firstChild);
		
		this.ratingElem.appendChild(createRating(rating));
		this.ratingElem.appendChild(
			document.createTextNode(SEO_RATING_LABEL[rating])
		);
		
		this.onKeywordsChange();
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
	 * Creates a new keyword and adds it to the keyword input
	 *
	 * @param {string} keyword
	 * @param {string=} rating
	 * @param {number|null=} index
	 */
	createKeyword = (keyword, rating = "neutral", index = null) => {
		const nextIndex = index !== null ? index : this.keywords.length;
		
		const elem = t("a", {
			href: "#",
			click: this.onKeywordClick,
			"data-index": String(nextIndex),
		}, [
			createRating(rating, "span"),
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
				rating,
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