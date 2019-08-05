/* globals Craft */
/**
 * Focus Keywords
 *
 * @author    Tam McDonald
 * @copyright Ether Creative 2017
 * @link      https://ethercreative.co.uk
 * @package   SEO
 * @since     2.0.0
 */

import { c, createRating, debounce } from '../helpers';

import KeywordChecklist from './KeywordChecklist';
import { SEO_RATING_LABEL } from '../const';

export default class FocusKeywords {
	
	// Variables
	// =========================================================================
	
	activeKeywordIndex = null;
	
	// FocusKeywords
	// =========================================================================
	
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
			document.getElementById(this.namespace + 'KeywordsInput');
		this.input = this.inputWrap.lastElementChild;
		
		// Events
		this.inputWrap.addEventListener('click', this.onInputWrapClick);
		this.input.addEventListener('focus', this.onInputFocus);
		this.input.addEventListener('blur', this.onInputBlur);
		this.input.addEventListener('keydown', this.onInputKeyDown);
	}
	
	/**
	 * Initializes the keywords (if any exist)
	 */
	initKeywords () {
		try {
			// Set initial keywords, adding the index variable
			this.keywords = JSON.parse(this.keywordsField.value).map((k, i) => {
				this.createKeyword(k.keyword, k.rating, i);
				
				return {
					...k,
					index: i,
				};
			});
		} catch (_) {
			this.keywords = [];
		}
		
		// Set the first keyword (if we have one) to be active
		this.keywords.length && this.setActiveKeyword(0);
		
		// Fire the keywords change callback
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
		if (!this.mo)
			return;
		
		let target = this.keywordsField.form;
		
		// If target is null that means we're in livePreview mode
		if (target === null && Craft.livePreview)
			target = Craft.livePreview.$editor[0];

		if (target === null)
			return;

		// TODO: Only want to watch form elements that will be posted
		this.mo.observe(target, {
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
		// If the index is out of bounds, nullify
		if (!this.keywords.length > index) this.activeKeywordIndex = null;
		
		// If the index is current, ignore
		if (this.activeKeywordIndex === index) return;
		
		// If we have an old index, deactivate it
		if (
			this.activeKeywordIndex !== null
		    && this.keywords.length > this.activeKeywordIndex
		) {
			this.getKeywordElementAtIndex(this.activeKeywordIndex)
			    .classList.remove('active');
		}
		
		// If our new index isn't out of bounds, activate it
		if (this.keywords.length > index) {
			this.activeKeywordIndex = index|0;
			this.getKeywordElementAtIndex(this.activeKeywordIndex)
			    .classList.add('active');
			
			// Re-calculate
			this.recalculateKeyword();
		} else {
			// Otherwise, clear the keywords readout
			this.keywordsChecklist.clear(this.onEmptyRating);
		}
	}
	
	/**
	 * Re-calculate the checklist & rating
	 */
	recalculateKeyword = () => {
		// Stop watching the form to prevent an update loop
		this.stopObserving();
		
		// If we have an active keyword, recalculate its details
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
		// Store the keywords in the hidden field, keep track of the ratings
		const ratingOccurrence = {};
		
		this.keywordsField.value = JSON.stringify(
			this.keywords.map(({ keyword, rating }) => {
				if (!ratingOccurrence.hasOwnProperty(rating))
					ratingOccurrence[rating] = 0;
				
				ratingOccurrence[rating]++;
				
				return { keyword, rating };
			})
		);
		
		// If we don't have any ratings, clear the hidden score field
		// TODO: Change the score field to rating
		if (!Object.keys(ratingOccurrence).length) {
			this.scoreField.value = '';
			return;
		}
		
		// Set the score field to the most prevalent rating
		this.scoreField.value =
			Object.keys(ratingOccurrence).reduce(
				(a, b) => ratingOccurrence[a] > ratingOccurrence[b] ? a : b
			);
	};
	
	/**
	 * Fired when the keyword checklist generates a new keyword rating
	 *
	 * @param {number} keywordIndex
	 * @param {string} rating
	 */
	onNewRating = (keywordIndex, rating) => {
		// Update the rating on the keyword
		const keyword = this.keywords[keywordIndex];
		
		// Catch, on the off chance we try and render after a rating was deleted
		if (!keyword) {
			this.onEmptyRating();
			return;
		}
		
		keyword.rating = rating;
		
		// Re-render keyword rating in input
		const elem = this.getKeywordElementAtIndex(keywordIndex);
		elem.removeChild(elem.firstElementChild);
		elem.insertBefore(createRating(rating, 'span'), elem.firstChild);
		
		// Set keyword details keyword
		this.keywordElem.textContent = keyword.keyword;
		
		// Set keyword details rating
		while (this.ratingElem.firstChild)
			this.ratingElem.removeChild(this.ratingElem.firstChild);
		
		this.ratingElem.appendChild(createRating(rating));
		this.ratingElem.appendChild(
			document.createTextNode(SEO_RATING_LABEL[rating])
		);
		
		// Fire the keywords change callback
		this.onKeywordsChange();
	};
	
	/**
	 * Fired when the keyword checklist is cleared
	 */
	onEmptyRating = () => {
		// Clear the keyword details keyword
		this.keywordElem.innerHTML = "<em>No keyword selected</em>";
		
		// Set keyword details rating to neutral
		while (this.ratingElem.firstChild)
			this.ratingElem.removeChild(this.ratingElem.firstChild);
		
		this.ratingElem.appendChild(createRating("neutral"));
		this.ratingElem.appendChild(
			document.createTextNode(SEO_RATING_LABEL["neutral"])
		);
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
		
		// Remove keyword
		const elem = this.getKeywordElementAtIndex(i);
		elem.parentNode.removeChild(elem);
		
		this.keywords.splice(i, 1);
		
		// Re-map indexes
		this.keywords = this.keywords.map((k, i) => {
			if (this.activeKeywordIndex === k.index)
				this.activeKeywordIndex = i;
			
			this.getKeywordElementAtIndex(i)
			    .setAttribute('data-index', i);
			
			return {
				...k,
				index: i,
			};
		});
		
		// If we're deleting the active keyword, reset to the first one
		this.activeKeywordIndex === i && this.setActiveKeyword(0);
		
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
		this.inputWrap.classList.add('focused');
	};
	
	/**
	 * Fired when the keywords input is blurred
	 */
	onInputBlur = e => {
		this.inputWrap.classList.remove('focused');

		if (e.target.value.trim() !== "") {
			this.onInputKeyDown({
				target: e.target,
				keyCode: 13,
				preventDefault: () => {},
			});
		}
	};
	
	/**
	 * Fired when a key is pressed while the keywords input is focused
	 *
	 * @param {Event} e
	 */
	onInputKeyDown = e => {
		if (e.keyCode !== 13 || e.key !== 'Enter') return;
		e.preventDefault();
		
		const nextKeyword = e.target.value.trim();
		let dupe = false;
		
		if (!nextKeyword) return;
		
		// Check if this is a duplicate and activate original if it is
		let i = this.keywords.length;
		while (i--) {
			let { keyword, index } = this.keywords[i];
			if (nextKeyword.toLowerCase() === keyword.toLowerCase()) {
				dupe = true;
				this.setActiveKeyword(index);
				break;
			}
		}
		
		// If it's not a duplicate, create a new keyword
		!dupe && this.createKeyword(nextKeyword);
		
		// Reset the input
		e.target.value = '';
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
	createKeyword = (keyword, rating = 'neutral', index = null) => {
		// Use the given index, or the next available one
		const nextIndex = index !== null ? index : this.keywords.length;
		
		// Create the keyword token
		const elem = c('a', {
			href: '#',
			click: this.onKeywordClick,
			'data-index': String(nextIndex),
		}, [
			createRating(rating, 'span'),
			keyword,
			c('object', {}, c('a', {
				href: '#',
				title: 'Remove',
				click: this.onKeywordRemoveClick,
			}, 'Remove'))
		]);
		
		// Add the keyword token to the input
		this.inputWrap.insertBefore(
			elem,
			this.inputWrap.lastElementChild
		);
		
		// If the given index is null (meaning a new keyword) store the keyword
		if (index === null) {
			this.keywords.push({
				keyword,
				rating,
				index: nextIndex,
			});
			
			// Make the new keyword active
			this.setActiveKeyword(nextIndex);
			
			// Fire the keywords change callback
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
