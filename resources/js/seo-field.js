/* global Craft, Garnish, $, module, define */

const SeoField = function (namespace, hasSection) {
	if (window.hasSeoField) return;
	
	window.hasSeoField = true;

	this.namespace = namespace;

	// Field
	document.getElementById(namespace + '-field').addEventListener('DOMNodeRemoved', function () {
		window.hasSeoField = false;
	});

	// Snippet
	this.title();
	this.slug();
	this.desc();

	if (hasSection) {

		this.getHTMLForParsing = new SeoField.GetEntryHTML();

		// Keyword
		this.keyword = document.getElementById(namespace + '-keyword');

		// Score
		this.score = document.getElementById(namespace + "-score");
		this.list = document.getElementById(namespace + "-list");
		this.bar = document.getElementById(namespace + "-bar");
		this.scoreField = document.getElementById(namespace + "-score-field");
		this.toggle();

		// Calculate
		this.calculateScore();

		// Re-calculate the score every second
		setInterval(() => {
			this.calculateScore();
		}, 1000);

	}
};

// SNIPPET
SeoField.prototype.title = function () {
	const title = this.titleField = document.getElementById(this.namespace + '-title')
		, t = document.getElementById('title');
	let tInput, titleInput, initial;

	tInput = function () {
		title.value = this.value + ' ' + initial;
	};

	titleInput = function () {
		this.classList.remove('clean');
		t.removeEventListener('input', tInput, false);
		this.removeEventListener('input', titleInput, false);
	};

	if (t && title.classList.contains('clean')) {
		initial = title.value;
		t.addEventListener('input', tInput, false);
	}

	title.addEventListener('input', titleInput, false);

	title.addEventListener('change', () => { this.calculateScore(); });
};

SeoField.prototype.slug = function () {
	const slug = this.slugField = document.getElementById(this.namespace + '-slug')
		, s = document.getElementById('slug');

	if (s && slug) {
		slug.textContent = s.value;

		// On a loop because crafts slug generation doesn't trigger any events
		setInterval(() => {
			if (slug.textContent !== s.value) {
				slug.textContent = s.value;
				this.calculateScore();
			}
		}, 1000);
	}
};

SeoField.prototype.desc = function () {
	const desc = this.descField = document.getElementById(this.namespace + '-description');

	function adjustHeight () {
		setTimeout(function () {
			desc.oninput();
		}, 1);
	}

	// Set Initial Height
	adjustHeight();

	// Set height on tab change (needed if the field is in a separate tab)
	if (document.getElementById('tabs')) {
		[].slice.call(document.querySelectorAll('#tabs a.tab')).forEach(function (el) {
			el.addEventListener('click', function () {
				adjustHeight();
			});
		});
	}

	if (Craft.livePreview) {
		Craft.livePreview.on('enter', adjustHeight);
		Craft.livePreview.on('exit', adjustHeight);
	}
	window.addEventListener('resize', adjustHeight);

	// Disable line breaks
	desc.addEventListener('keydown', function (e) {
		if (e.keyCode === 13) e.preventDefault();
	});

	// Cleanse line breaks and check length
	desc.addEventListener('input', function () {
		this.value = this.value.replace(/(\r\n|\n|\r)/gm," ");
		if (this.value.length > 160) this.classList.add('invalid');
		else this.classList.remove('invalid');
	});

	desc.addEventListener('change', () => { this.calculateScore(); });
};

// SCORE
SeoField.prototype.toggle = function () {
	let isOpen = false;

	this.score.getElementsByClassName('toggle-score')[0].addEventListener('click', () => {
		this.score.classList.toggle('open');
		isOpen = !isOpen;

		if (isOpen) {
			this.score.getElementsByClassName('details')[0].style.height = this.score.getElementsByClassName('details-inner')[0].clientHeight + 'px';
		} else {
			this.score.getElementsByClassName('details')[0].style.height = '';
		}
	});

	this.toggle.close = () => {
		this.score.classList.remove('open');
		isOpen = false;
		this.score.getElementsByClassName('details')[0].style.height = '';
	};
};

SeoField.prototype.calculateScore = function () {
	if (this.keyword && this.keyword.value) {
		this.currentScore = {};

		this.currentScore.titleLength = this.judgeTitleLength();
		this.currentScore.titleKeyword = this.judgeTitleKeyword();
		this.currentScore.slug = this.judgeSlug();
		this.currentScore.desc = this.judgeDesc();

		this.getHTMLForParsing.update((content) => {
			if (content.textContent.replace('\r', '').replace('\n', '').replace('\r\n', '').replace(/\s+/gi, '') === '') {
				this.currentScore.noContent = {
					score: SeoField.Levels.BAD,
					reason: 'You have no content, adding some would be a good start!'
				};
			} else {
				this.content = content;
				this.content.textOnly = this.content.textContent.replace('\r', ' ').replace('\n', ' ').replace('\r\n', ' ').replace(/\s+/gi, ' ');
				this.content.stats = SeoField.TextStatistics(this.content.textContent);

				this.currentScore.wordCount = this.judgeWordCount();
				this.currentScore.firstParagraph = this.judgeFirstParagraph();
				this.currentScore.images = this.judgeImages();
				this.currentScore.links = this.judgeLinks();
				this.currentScore.headings = this.judgeHeadings();
				this.currentScore.density = this.judgeDensity();
				this.currentScore.fleschEase = this.judgeFleschEase();
			}

			this.getHTMLForParsing.clean();
			this.updateScoreHtml();
		});
	} else {
		this.toggle.close();
		this.score.classList.add('disabled');
		this.scoreField.value = '';
	}
};

SeoField.prototype.updateScoreHtml = function () {
	this.score.classList.remove('disabled');

	const sorted = SeoField.sortScore(this.currentScore);
	const sortedScore = sorted.merged;

	this.list.innerHTML = '';
	for (let i = 0; i < sortedScore.length; i++) {
		const j = sortedScore[i];
		this.list.innerHTML += '<li class="'+ j.score+'">' + j.reason + '</li>';
	}

	const good = this.bar.getElementsByClassName('good')[0]
		, ok = this.bar.getElementsByClassName('ok')[0]
		, bad = this.bar.getElementsByClassName('bad')[0];

	const goodW = 1 - ((sortedScore.length - sorted.good.length) / sortedScore.length)
		, okW = 1 - ((sortedScore.length - sorted.ok.length) / sortedScore.length)
		, badW = 1 - ((sortedScore.length - sorted.bad.length) / sortedScore.length);

	good.style.transform = 'scale(' + goodW + ', 1)';
	ok.style.transform = 'translateX(' + (goodW * 100) + '%) scale(' + okW + ', 1)';
	bad.style.transform = 'translateX(' + ((goodW + okW) * 100) + '%) scale(' + badW + ', 1)';

	let s = '';

	if (badW > goodW) {
		s = 'bad';
	} else if ((badW + okW) > goodW) {
		s = 'ok';
	} else {
		s = 'good';
	}

	this.scoreField.value = s;
};

// CALCULATOR
SeoField.prototype.judgeTitleLength = function () {
	const v = this.titleField.value;
	
	let ret = {
		score : (v.length < 40 || v.length > 60) ? SeoField.Levels.BAD : SeoField.Levels.GOOD,
		reason: (v.length < 40) ? SeoField.Reasons.titleLengthFailMin : (v.length > 60) ? SeoField.Reasons.titleLengthFailMax : SeoField.Reasons.titleLengthSuccess
	};
	ret.reason = ret.reason.replace('{l}', v.length);

	return ret;
};

SeoField.prototype.judgeTitleKeyword = function () {
	let ret;
	const index = this.titleField.value.toLowerCase().indexOf(this.keyword.value.toLowerCase());

	if (index > -1) {
		if (index <= this.titleField.value.length * 0.3) {
			ret = {
				score : SeoField.Levels.GOOD,
				reason: SeoField.Reasons.titleKeywordSuccess
			};
		} else {
			ret = {
				score : SeoField.Levels.OK,
				reason: SeoField.Reasons.titleKeywordPosFail
			};
		}
	} else {
		ret = {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.titleKeywordFail
		};
	}

	return ret;
};

SeoField.prototype.judgeSlug = function () {
	if (!this.slugField) return;

	console.log(this.keyword.value.toLowerCase().replace(/[^a-zA-Z0-9-_]/g, ''));
	if (this.slugField.textContent.toLowerCase().indexOf(this.keyword.value.toLowerCase().replace(/[^a-zA-Z0-9-_]/g, '')) > -1) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.slugSuccess
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.slugFail
		};
	}
};

// TODO: Check if keyword in first half / number of times it appears?
SeoField.prototype.judgeDesc = function () {
	if (this.descField.value.toLowerCase().indexOf(this.keyword.value.toLowerCase()) > -1) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.descSuccess
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.descFail
		};
	}
};

SeoField.prototype.judgeWordCount = function () {
	var wc = this.content.stats.wordCount();
	if (wc > 300) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.wordCountSuccess.replace('{l}', wc)
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.wordCountFail.replace('{l}', wc)
		};
	}
};

SeoField.prototype.judgeFirstParagraph = function () {
	if (this.content.querySelector('p') && this.content.querySelector('p').textContent.toLowerCase().indexOf(this.keyword.value.toLowerCase()) > -1) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.firstParagraphSuccess
		};
	} else {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.firstParagraphFail
		};
	}
};

SeoField.prototype.judgeImages = function () {
	const imgs = this.content.getElementsByTagName('img');
	if (imgs) {
		let imgsWithAltKeyword = 0;

		for (let i = 0; i < imgs.length; i++) {
			if (imgs[i].getAttribute('alt') &&
				imgs[i].getAttribute('alt').toLowerCase().indexOf(this.keyword.value.toLowerCase()))
				imgsWithAltKeyword++;
		}

		if (imgsWithAltKeyword === imgs.length) {
			return {
				score : SeoField.Levels.GOOD,
				reason: SeoField.Reasons.imagesSuccess
			};
		} else if (imgsWithAltKeyword >= imgs.length/2) {
			return {
				score : SeoField.Levels.OK,
				reason: SeoField.Reasons.imagesOk
			};
		} else {
			return {
				score : SeoField.Levels.BAD,
				reason: SeoField.Reasons.imagesFail
			};
		}
	}
};

SeoField.prototype.judgeLinks = function () {
	const a = this.content.getElementsByTagName('a');

	if (a) {
		for (let i = 0; i < a.length; i++) {
			if (SeoField.isExternalUrl(a[i].href)) {
				return {
					score : SeoField.Levels.GOOD,
					reason: SeoField.Reasons.linksSuccess
				};
			}
		}
	}

	return {
		score : SeoField.Levels.BAD,
		reason: SeoField.Reasons.linksFail
	};
};

SeoField.prototype.judgeHeadings = function () {
	const headings = this.content.querySelectorAll('h1,h2,h3,h4,h5,h6');

	if (headings) {
		let primary = 0, secondary = 0;

		for (let i = 0; i < headings.length; i++) {
			if (headings[i].textContent.toLowerCase().indexOf(this.keyword.value.toLowerCase()) > -1) {
				if (['H1', 'H2'].indexOf(headings[i].nodeName) > -1) {
					primary++;
				} else {
					secondary++;
				}
			}
		}

		if (primary > 0) {
			return {
				score : SeoField.Levels.GOOD,
				reason: SeoField.Reasons.headingsSuccess
			};
		} else if (secondary > 0) {
			return {
				score : SeoField.Levels.OK,
				reason: SeoField.Reasons.headingsOk
			};
		}
	}

	return {
		score : SeoField.Levels.BAD,
		reason: SeoField.Reasons.headingsFail
	};
};

SeoField.prototype.judgeDensity = function () {
	const words = this.content.stats.words();

	function countInArray (arr, word) {
		let c = 0, i = 0;
		for (; i < arr.length; i++)
			if (arr[i].toLowerCase() === word) c++;
		return c;
	}

	const keyCount = countInArray(words, this.keyword.value.toLowerCase());

	const keyPercent = parseFloat((100 + ((keyCount - words.length) / words.length) * 100).toFixed(2));

	if (keyPercent < 1.0) {
		return {
			score : SeoField.Levels.BAD,
			reason: SeoField.Reasons.densityFailUnder.replace('{d}', keyPercent)
		};
	} else if (keyPercent <= 2.5) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.densitySuccess.replace('{d}', keyPercent)
		};
	} else if (keyPercent > 2.5) {
		return {
			score : SeoField.Levels.OK,
			reason: SeoField.Reasons.densityOk.replace('{d}', keyPercent).replace('{c}', keyCount)
		};
	}

	return {
		score : SeoField.Levels.BAD,
		reason: SeoField.Reasons.densityFail
	};
};

SeoField.prototype.judgeFleschEase = function () {
	const level = this.content.stats.fleschKincaidReadingEase();

	if (level >= 80) {
		return {
			score : SeoField.Levels.GOOD,
			reason: SeoField.Reasons.fleschSuccess.replace('{l}', level)
		};
	} else if (level >= 60) {
		return {
			score : SeoField.Levels.OK,
			reason: SeoField.Reasons.fleschOk.replace('{l}', level)
		};
	}

	return {
		score : SeoField.Levels.BAD,
		reason: SeoField.Reasons.fleschFail.replace('{l}', level)
	};
};

// HELPERS
SeoField.Fail = function (message) {
	Craft.cp.displayError('<strong>SEO:</strong> ' + message);
	if (window.console) console.error.apply(console, ['%cSEO: %c' + message, 'font-weight:bold;','font-weight:normal;']); // eslint-disable-line no-console
};

/**
 * Get Parsed Fields HTML
 */
SeoField.GetEntryHTML = function () {
	this.clean();
};

SeoField.GetEntryHTML.prototype.update = function (cb) {
	const postData = Garnish.getPostData(document.getElementById('container'));

	if (typeof Craft.livePreview === typeof undefined) {
		this.score.previousElementSibling.classList.add('hide');
		return;
	}

	if (!this.lastPostData || !Craft.compare(postData, this.lastPostData)) {
		this.lastPostData = postData;

		$.ajax({
			url: Craft.livePreview.previewUrl,
			method: 'POST',
			data: $.extend({}, postData, Craft.livePreview.basePostData),
			xhrFields: {
				withCredentials: true
			},
			crossDomain: true,
			success: (data) => {
				data = data.replace(/<script([^'"]|"(\\.|[^"\\])*"|'(\\.|[^'\\])*')*?<\/script>/g, '');

				this.iframe.contentWindow.document.open();
				this.iframe.contentWindow.document.write(data);
				this.iframe.contentWindow.document.close();

				if (this.iframe.contentWindow.document.body)
					cb(this.iframe.contentWindow.document.body);
				else
					SeoField.Fail("Unable to calculate score");
			}
		});
	}
};

SeoField.GetEntryHTML.prototype.clean = function () {
	if (this.iframe)
		document.body.removeChild(this.iframe);

	this.iframe = document.createElement('iframe');
	this.iframe.setAttribute('frameborder', '0');
	this.iframe.style.width = '0px';
	this.iframe.style.height = '0px';
	document.body.appendChild(this.iframe);
};

/**
 * External URL checker
 * From http://stackoverflow.com/a/9744104/550109
 *
 * @param {string} url
 */
SeoField.isExternalUrl = (function(){
	const domainRe = /https?:\/\/((?:[\w\d]+\.)+[\w\d]{2,})/i;
	let res = null;

	return function(url) {
		function domain(url) {
			return (res = domainRe.exec(url)) !== null ? res[1] : false;
		}

		return domain(location.href) !== domain(url);
	};
})();

/**
 * Organize the judgements by score
 *
 * @param unsorted
 * @returns {{good: Array, ok: Array, bad: Array, merged: Array.<T>}}
 */
SeoField.sortScore = function (unsorted) {
	const good = [], ok = [], bad = [];
	for (let key in unsorted) {
		if (unsorted.hasOwnProperty(key) && unsorted[key]) {
			unsorted[key].key = key;
			switch (unsorted[key].score) {
				case SeoField.Levels.BAD:
					bad.push(unsorted[key]);
					break;
				case SeoField.Levels.OK:
					ok.push(unsorted[key]);
					break;
				case SeoField.Levels.GOOD:
					good.push(unsorted[key]);
					break;
			}
		}
	}

	return {
		good: good,
		ok: ok,
		bad: bad,
		merged: good.concat(ok.concat(bad))
	};
};

/**
 * TextStatistics.js
 * Christopher Giffard (2012)
 * 1:1 API Fork of TextStatistics.php by Dave Child (Thanks mate!)
 * https://github.com/DaveChild/Text-Statistics
 *
 * Modified by Tam<hi@tam.sx>
 */
(function(glob) {

	function cleanText(text) {
		// all these tags should be preceeded by a full stop.
		const fullStopTags = ['li', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'dd'];

		fullStopTags.forEach(function(tag) {
			text = text.replace("</" + tag + ">",".");
		});

		text = text
			.replace(/<[^>]+>/g, "")				// Strip tags
			.replace(/[,:;()\-]/, " ")				// Replace commas, hyphens etc (count them as spaces)
			.replace(/’/g, "'")                     // Replace ’ with '
			.replace(/[\.!?]/, ".")					// Unify terminators
			.replace(/^\s+/,"")						// Strip leading whitespace
			.replace(/[ ]*(\n|\r\n|\r)[ ]*/," ")	// Replace new lines with spaces
			.replace(/([\.])[\. ]+/,".")			// Check for duplicated terminators
			.replace(/[ ]*([\.])/,". ")				// Pad sentence terminators
			.replace(/\s+/," ")						// Remove multiple spaces
			.replace(/\s+$/,"");					// Strip trailing whitespace

		text += "."; // Add final terminator, just in case it's missing.

		return text;
	}

	const TextStatistics = function TextStatistics(text) {
		this.text = text ? cleanText(text) : this.text;
	};

	TextStatistics.prototype.fleschKincaidReadingEase = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round((206.835 - (1.015 * this.averageWordsPerSentence(text)) - (84.6 * this.averageSyllablesPerWord(text)))*10)/10;
	};

	TextStatistics.prototype.fleschKincaidGradeLevel = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round(((0.39 * this.averageWordsPerSentence(text)) + (11.8 * this.averageSyllablesPerWord(text)) - 15.59)*10)/10;
	};

	TextStatistics.prototype.gunningFogScore = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round(((this.averageWordsPerSentence(text) + this.percentageWordsWithThreeSyllables(text, false)) * 0.4)*10)/10;
	};

	TextStatistics.prototype.colemanLiauIndex = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round(((5.89 * (this.letterCount(text) / this.wordCount(text))) - (0.3 * (this.sentenceCount(text) / this.wordCount(text))) - 15.8 ) *10)/10;
	};

	TextStatistics.prototype.smogIndex = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round(1.043 * Math.sqrt((this.wordsWithThreeSyllables(text) * (30 / this.sentenceCount(text))) + 3.1291)*10)/10;
	};

	TextStatistics.prototype.automatedReadabilityIndex = function(text) {
		text = text ? cleanText(text) : this.text;
		return Math.round(((4.71 * (this.letterCount(text) / this.wordCount(text))) + (0.5 * (this.wordCount(text) / this.sentenceCount(text))) - 21.43)*10)/10;
	};

	TextStatistics.prototype.textLength = function(text) {
		text = text ? cleanText(text) : this.text;
		return text.length;
	};

	TextStatistics.prototype.letterCount = function(text) {
		text = text ? cleanText(text) : this.text;
		text = text.replace(/[^a-z]+/ig,"");
		return text.length;
	};

	TextStatistics.prototype.sentenceCount = function(text) {
		text = text ? cleanText(text) : this.text;

		// Will be tripped up by "Mr." or "U.K.". Not a major concern at this point.
		return text.replace(/[^\.!?]/g, '').length || 1;
	};

	TextStatistics.prototype.wordCount = function(text) {
		text = text ? cleanText(text) : this.text;
		return text.split(/[^a-z0-9']+/i).length || 1;
	};

	TextStatistics.prototype.words = function () {
		if (this._words) return this._words;
		return this._words = this.text.split(/[^a-z0-9']+/i); // jshint ignore:line
	};

	TextStatistics.prototype.averageWordsPerSentence = function(text) {
		text = text ? cleanText(text) : this.text;
		return this.wordCount(text) / this.sentenceCount(text);
	};

	TextStatistics.prototype.averageSyllablesPerWord = function(text) {
		text = text ? cleanText(text) : this.text;
		let syllableCount = 0, wordCount = this.wordCount(text);

		text.split(/\s+/).forEach((word) => {
			syllableCount += this.syllableCount(word);
		});

		// Prevent NaN...
		return (syllableCount||1) / (wordCount||1);
	};

	TextStatistics.prototype.wordsWithThreeSyllables = function(text, countProperNouns) {
		text = text ? cleanText(text) : this.text;
		let longWordCount = 0;

		countProperNouns = countProperNouns !== false;

		text.split(/\s+/).forEach((word) => {

			// We don't count proper nouns or capitalised words if the countProperNouns attribute is set.
			// Defaults to true.
			if (!word.match(/^[A-Z]/) || countProperNouns) {
				if (this.syllableCount(word) > 2) longWordCount ++;
			}
		});

		return longWordCount;
	};

	TextStatistics.prototype.percentageWordsWithThreeSyllables = function(text, countProperNouns) {
		text = text ? cleanText(text) : this.text;

		return (this.wordsWithThreeSyllables(text,countProperNouns) / this.wordCount(text)) * 100;
	};

	TextStatistics.prototype.syllableCount = function(word) {
		let syllableCount = 0,
			prefixSuffixCount = 0,
			wordPartCount = 0;

		// Prepare word - make lower case and remove non-word characters
		word = word.toLowerCase().replace(/[^a-z]/g,"");

		// Specific common exceptions that don't follow the rule set below are handled individually
		// Array of problem words (with word as key, syllable count as value)
		const problemWords = {
			"simile":		3,
			"forever":		3,
			"shoreline":	2
		};

		// Return if we've hit one of those...
		if (problemWords.hasOwnProperty(word)) return problemWords[word];

		// These syllables would be counted as two but should be one
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
			/^[dr]e[aeiou][^aeiou]+$/, // Sorts out deal, deign etc
			/[aeiouy]rse$/ // Purse, hearse
		];

		// These syllables would be counted as one but should be two
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

		// Single syllable prefixes and suffixes
		const prefixSuffix = [
			/^un/,
			/^fore/,
			/ly$/,
			/less$/,
			/ful$/,
			/ers?$/,
			/ings?$/
		];

		// Remove prefixes and suffixes and count how many were taken
		prefixSuffix.forEach(function(regex) {
			if (word.match(regex)) {
				word = word.replace(regex,"");
				prefixSuffixCount ++;
			}
		});

		wordPartCount = word
			.split(/[^aeiouy]+/ig)
			.filter(function(wordPart) {
				return !!wordPart.replace(/\s+/ig,"").length;
			})
			.length;

		// Get preliminary syllable count...
		syllableCount = wordPartCount + prefixSuffixCount;

		// Some syllables do not follow normal rules - check for them
		subSyllables.forEach(function(syllable) {
			if (word.match(syllable)) syllableCount --;
		});

		addSyllables.forEach(function(syllable) {
			if (word.match(syllable)) syllableCount ++;
		});

		return syllableCount || 1;
	};

	function textStatistics(text) {
		return new TextStatistics(text);
	}

	(typeof module != "undefined" && module.exports) ? (module.exports = textStatistics) : (typeof define != "undefined" ? (define("textstatistics", [], function() { return textStatistics; })) : (glob.TextStatistics = textStatistics)); // jshint ignore:line
})(SeoField);

// CONSTS / ENUMS
SeoField.Levels = {
	NONE: '',
	GOOD: 'good',
	OK: 'ok',
	BAD: 'bad'
};

SeoField.Reasons = {
	titleLengthFailMin: 'The title contains {l} characters which is less than the recommended minimum of 40 characters.',
	titleLengthFailMax: 'The title contains {l} characters which is greater than the recommended maximum of 60 characters.',
	titleLengthSuccess: 'The title is between the recommended minimum and maximum length.',

	titleKeywordFail: 'The title does not contain the keyword. Try adding it near the beginning of the title.',
	titleKeywordSuccess: 'The title contains the keyword near the beginning.',
	titleKeywordPosFail: 'The title contains the keyword, but not near the beginning. Try to move it closer to the start of the title.',

	slugFail: 'The URL does not contain the keyword. Try adding it to the slug.',
	slugSuccess: 'The URL contains the keyword.',

	descFail: 'The description does not contain the keyword. Try adding it near the beginning of the description.',
	descSuccess: 'The description contains the keyword.',

	wordCountFail: 'Your text contains {l} words, this is less than the recommended 300 word minimum.',
	wordCountSuccess: 'Your text contains {l} words, this is more than the recommended 300 word minimum.',

	firstParagraphFail: 'The keyword does not appear in the first paragraph of your text. Try adding it.',
	firstParagraphSuccess: 'The keyword appears in the first paragraph of your text.',

	imagesFail: 'Less than half of the images have alt tags containing the keyword, try adding it to more images.',
	imagesOk: 'Half or more of the images have alt tags containing the keyword. To improve this, try adding keywords to all the images alt tags.',
	imagesSuccess: 'All of the images have alt tags containing the keyword.',

	linksFail: 'The page does not contain any outgoing links. Try adding some.',
	linksSuccess: 'The page contains outgoing links.',

	headingsFail: 'The page does not contain any headings that contain the keyword. Try adding some with the keyword.',
	headingsOk: 'The page contains some lower importance headings that contain the keyword. Try adding the keyword to some h2\'s.',
	headingsSuccess: 'The page contains higher importance headings with the keyword.',

	densityFail: 'The keyword does not appear in the text. It is important to include it in your content.',
	densityFailUnder: 'The keyword density is {d}%, which is well under the advised 2.5%. Try increasing the number of times the keyword is used.',
	densityOk: 'The keyword density is {d}%, which is over the advised 2.5%. The keyword appears {c} times.',
	densitySuccess: 'The keyword density is {d}%, which is near the advised 2.5%.',

	fleschFail: 'The Flesch Reading ease score is {l} which is considered best for university graduates. Try reducing your sentence length to improve readability.',
	fleschOk: 'The Flesch Reading ease score is {l} which is average, and considered easily readable by most users.',
	fleschSuccess: 'The Flesch Reading ease score is {l} meaning your content is readable by all ages.',
};
