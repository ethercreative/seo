/**
 * TextStatistics.js
 * Christopher Giffard (2012)
 * 1:1 API Fork of TextStatistics.php by Dave Child (Thanks mate!)
 * https://github.com/DaveChild/Text-Statistics
 *
 * Modified & re-written for ES6 by Tam<hi@tam.sx>
 */

export default class TextStatistics {
	
	constructor (text) {
		this.text = text ? TextStatistics.cleanText(text) : text;
	}
	
	// Statistics
	// =========================================================================
	
	/**
	 * Calculates the Flesch Kincaid reading ease score
	 *
	 * @return {number}
	 */
	fleschKincaidReadingEase () {
		return Math.round(
			(
				206.835
				- (1.015 * this.averageWordsPerSentence())
				- (84.6 * this.averageSyllablesPerWord())
			) * 10
		) / 10;
	}
	
	/**
	 * Calculates the Flesch Kincaid grade level
	 *
	 * @return {number}
	 */
	fleschKincaidGradeLevel () {
		return Math.round(
			(
				(0.39 * this.averageWordsPerSentence())
				+ (11.8 * this.averageSyllablesPerWord())
				- 15.59
			) * 10
		) / 10;
	}
	
	/**
	 * Calculates the Gunning Fog score
	 *
	 * @return {number}
	 */
	gunningFogScore () {
		return Math.round(
			(
				(
					this.averageWordsPerSentence()
					+ this.percentageWordsWithThreeSyllables(false)
				) * 0.4
			) * 10
		) / 10;
	}
	
	/**
	 * Calculates the Coleman Liau index
	 *
	 * @return {number}
	 */
	colemanLiauIndex () {
		return Math.round(
			(
				(
					5.89
					* (this.letterCount() / this.wordCount())
				) - (
					0.3
					* (this.sentenceCount() / this.wordCount())
				) - 15.8
			) * 10
		) / 10;
	}
	
	/**
	 * Calculates the Smog index
	 *
	 * @return {number}
	 */
	smogIndex () {
		return Math.round(
			1.043
			* Math.sqrt(
				(
					this.wordsWithThreeSyllables()
					* (30 / this.sentenceCount())
				) + 3.1291
			) * 10
		) / 10;
	}
	
	/**
	 * Calculates the Automated Readability index
	 *
	 * @return {number}
	 */
	automatedReadabilityIndex () {
		return Math.round(
			(
				(
					4.71
					* (this.letterCount() / this.wordCount())
				) + (
					0.5
					* (this.wordCount() / this.sentenceCount())
				) - 21.43
			) * 10
		) / 10;
	}
	
	// Helpers
	// =========================================================================
	
	/**
	 * Cleans the text for processing
	 *
	 * @param {string} text - Text to clean
	 * @return {string}
	 */
	static cleanText (text) {
		// All these tags should be preceded by a full stop
		['li', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'dd'].forEach(tag => {
			text.replace(`</${tag}>`, ".");
		});
		
		text = text
				// Strip tags
				.replace(/<[^>]+>/g, '')
				
				// Replace commas, hyphens etc (count them as spaces)
				.replace(/[,:;()-]/g, ' ')
				
				// Unify terminators
		        .replace(/[.!?]/g, '.')
				
				// Strip leading whitespace
				.replace(/^\s+/g, '')
				
				// Replace new lines with spaces
				.replace(/[ ]*(\n|\r\n|\r)[ ]*/g, ' ')
				
				// Check for duplicated terminators
				.replace(/([.])[. ]+/g, '.')
				
				// Pad sentence terminators
				.replace(/[ ]*([.])/g, '. ')
				
				// Remove multiple spaces
				.replace(/\s+/g, ' ')
				
				// Strip trailing whitespace
				.replace(/\s+$/g, '')
				
				// Strip apostrophe
				.replace(/'/g, '');
		
		// Add final terminator, just in case it's missing
		text += ".";
		
		return text;
	}
	
	/**
	 * Counts the number of syllables a given word
	 *
	 * @param {string} word
	 * @return {number}
	 */
	static syllableCount (word) {
		let syllableCount = 0,
			prefixSuffixCount = 0;
		
		// Prepare word - make lower case & remove non-word characters
		word = word.toLowerCase().replace(/[^a-z]/g, '');
		
		// Specific common exceptions that don't follow the rule set below are
		// handled individually.
		// Array of problem words (with word as key, syllable count as value)
		const problemWords = {
			'simile':       3,
			'forever':      3,
			'shoreline':    2,
		};
		
		// Return if we've hit a problem word
		if (problemWords.hasOwnProperty(word)) return problemWords[word];
		
		// Syllables that would be counted as two, but should be one
		const subSyllables = [
			/cial/,
			/tia/,
			/cius/,
			/cious/,
			/giu/,
			/ion/,
			/iou/,
			/sia$/,
			/[^aeiuoyt]{2,}ed$/,
			/.ely$/,
			/[cg]h?e[rsd]?$/,
			/rved?$/,
			/[aeiouy][dt]es?$/,
			/[aeiouy][^aeiouydt]e[rsd]?$/,
			/^[dr]e[aeiou][^aeiou]+$/,      // Sorts out deal, deign etc.
			/[aeiouy]rse$/,                 // Purse, hearse
		];
		
		// Syllables that would be counted as one, but should be two
		const addSyllables = [
			/ia/,
			/riet/,
			/dien/,
			/iu/,
			/io/,
			/ii/,
			/[aeiouym]bl$/,
			/[aeiou]{3}/,
			/^mc/,
			/ism$/,
			/([^aeiouy])\1l$/,
			/[^l]lien/,
			/^coa[dglx]./,
			/[^gq]ua[^auieo]/,
			/dnt$/,
			/uity$/,
			/ie(r|st)$/
		];
		
		// Single syllable prefixes & suffixes
		const prefixSuffix = [
			/^un/,
			/^fore/,
			/ly$/,
			/less$/,
			/ful$/,
			/ers?$/,
			/ings?$/
		];
		
		// Remove prefixes & suffixes, and count how many were takes
		prefixSuffix.forEach(regex => {
			if (word.match(regex)) {
				word = word.replace(regex, '');
				prefixSuffixCount++;
			}
		});
		
		let wordPartCount = word
			.split(/[^aeiouy]+/ig)
			.filter(wordPart => !!wordPart.replace(/\s+/ig, '').length)
			.length;
		
		// Get preliminary syllable count
		syllableCount = wordPartCount + prefixSuffixCount;
		
		// Some syllables do not follow normal rules, check for them
		subSyllables.forEach(syllable => {
			word.match(syllable) && syllableCount--;
		});
		
		addSyllables.forEach(syllable => {
			word.match(syllable) && syllableCount++;
		});
		
		return syllableCount || 1;
	}
	
	/**
	 * Returns the length of the text
	 *
	 * @return {Number}
	 */
	textLength () {
		return this.text.length;
	}
	
	/**
	 * Counts the number of letters in the text
	 *
	 * @return {Number}
	 */
	letterCount () {
		return this.text.replace(/[^a-z]+/ig, '').length;
	}
	
	/**
	 * Counts the number of sentences in the text
	 *
	 * @return {Number|number}
	 */
	sentenceCount () {
		// FIXME: This will be tripped up by "Mr." or "U.K."
		return this.text.replace(/[^.!?]/g, '').length || 1;
	}
	
	/**
	 * Counts the number of words in the text
	 *
	 * @return {number}
	 */
	wordCount () {
		return this.words().length || 1;
	}
	
	/**
	 * Splits the text into an array of words.
	 *
	 * @return {Array}
	 */
	words () {
		if (this._words) return this._words;
		this._words = this.text.split(/[^a-z0-9']+/i);
		return this._words;
	}
	
	/**
	 * Calculates the average number of words per sentence
	 *
	 * @return {number}
	 */
	averageWordsPerSentence () {
		return this.wordCount() / this.sentenceCount();
	}
	
	/**
	 * Calculates the average number of syllables per word
	 *
	 * @return {number}
	 */
	averageSyllablesPerWord () {
		let syllableCount = 0,
			wordCount = this.wordCount();
		
		this.text.split(/\s+/).forEach(word => {
			syllableCount += TextStatistics.syllableCount(word);
		});
		
		return (syllableCount || 1) / (wordCount || 1);
	}
	
	/**
	 * Counts the number of words in the text w/ three syllables
	 *
	 * @param {boolean} countProperNouns - If true, will ignore proper nouns or
	 *     capitalized words.
	 * @return {number}
	 */
	wordsWithThreeSyllables (countProperNouns = true) {
		let longWordCount = 0;
		
		countProperNouns = countProperNouns !== false;
		
		this.text.split(/\s+/).forEach(word => {
			// We don't count proper nouns or capitalized words if the
			// `countProperNouns` argument is set (defaults to true).
			if (!word.match(/^[A-Z]/) || countProperNouns) {
				if (this.syllableCount(word) > 2) longWordCount++;
			}
		});
		
		return longWordCount;
	}
	
	/**
	 * Calculates the percentage of words with three syllables
	 *
	 * @param {boolean} countProperNouns - If true, will ignore proper nouns or
	 *     capitalized words.
	 * @return {number}
	 */
	percentageWordsWithThreeSyllables (countProperNouns = true) {
		return (
			this.wordsWithThreeSyllables(countProperNouns) / this.wordCount()
        ) * 100;
	}
	
}