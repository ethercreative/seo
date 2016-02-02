var SeoField = function (namespace, readabilityFields) {
	var self = this;

	this.namespace = namespace;
	this.readabilityFields = readabilityFields;

	// Snippet
	// TODO: Make keyword (or available parts of) bold in snippet title / desc
	this.title();
	this.slug();
	this.desc();

	// Keyword
	this.keyword = document.getElementById(namespace + '-keyword');
	this.keyword.addEventListener('change', function () {
		self.keywordListener();
	});

	// Score
	this.score = document.getElementById(namespace + "-score");
	this.toggle();

	// Calculate // TODO: Fire on ANY field change
	this.calculateScore();
};

// SNIPPET
SeoField.prototype.title = function () {
	var title = this.titleField = document.getElementById(this.namespace + '-title'),
		t = document.getElementById('title'),
		tInput, titleInput, self = this;

	tInput = function () {
		title.value = this.value + ' ' + initial;
	};

	titleInput = function () {
		this.classList.remove('clean');
		t.removeEventListener('input', tInput, false);
		this.removeEventListener('input', titleInput, false);
	};

	if (t && title.classList.contains('clean')) {
		var initial = title.value;
		t.addEventListener('input', tInput, false);
	}

	title.addEventListener('input', titleInput, false);

	title.addEventListener('change', function () { self.calculateScore(); });
};

SeoField.prototype.slug = function () {
	var slug = this.slugField = document.getElementById(this.namespace + '-slug'),
		s = document.getElementById('slug'),
		r = document.getElementById(this.namespace + '-ref'),
		self = this;

	if (s && slug) {
		slug.textContent = s.value;
		r.textContent = r.textContent.replace(s.value, '');

		// On a loop because crafts slug generation doesn't trigger any events
		setInterval(function () {
			if (slug.textContent !== s.value) {
				slug.textContent = s.value;
				self.calculateScore();
			}
		}, 1000);
	}
};

SeoField.prototype.desc = function () {
	var desc = this.descField = document.getElementById(this.namespace + '-description'),
		self = this;

	function adjustHeight () {
		desc.oninput();
	}

	// Set Initial Height
	setTimeout(function () {
		adjustHeight();
	}, 1);

	Craft.livePreview.on('enter', adjustHeight);
	Craft.livePreview.on('exit', adjustHeight);
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

	desc.addEventListener('change', function () { self.calculateScore(); });
};

// KEYWORD
SeoField.prototype.keywordListener = function () {
	this.calculateScore();
};

// SCORE
SeoField.prototype.toggle = function () {
	var self = this,
		isOpen = false;

	this.score.getElementsByClassName('toggle-score')[0].addEventListener('click', function () {
		self.score.classList.toggle('open');
		isOpen = !isOpen;

		if (isOpen) {
			self.score.getElementsByClassName('details')[0].style.height = self.score.getElementsByClassName('details-inner')[0].clientHeight + 'px';
		} else {
			self.score.getElementsByClassName('details')[0].style.height = '';
		}

		//self.moveItems(isOpen);
	});
};
// FIXME
SeoField.prototype.moveItems = function (isOpen) {
	var items = [].slice.call(this.score.getElementsByClassName('item')),
		details = this.score.querySelectorAll('.details li');

	[].slice.call(details).forEach(function (detail) {
		var i = detail.getAttribute('data-i');

		if (isOpen) {
			items[i].style.left = detail.offsetLeft + 'px';
			items[i].style.top = detail.offsetTop + 'px';
		} else {
			items[i].style.left = (i * (100 / items.length)) + '%';
			items[i].style.top = '';
		}
	});
};

SeoField.prototype.calculateScore = function () {
	if (this.keyword.value) {
		this.currentScore = {};
		var self = this;

		this.currentScore.titleLength = this.judgeTitleLength();
		this.currentScore.titleKeyword = this.judgeTitleKeyword();
		this.currentScore.slug = this.judgeSlug();
		this.currentScore.desc = this.judgeDesc();

		if (this.readabilityFields.length > 0) {
			SeoField.getFieldsHTML(this.readabilityFields, function (res) {
				var content = document.createElement('div');
				content.innerHTML = res;

				[].slice.call(content.getElementsByTagName('seo-parse')).forEach(function (el) {
					if (!el.textContent || el.textContent === 'Array') {
						content.removeChild(el);
					}
				});

				self.content = content;

				self.currentScore.wordCount = self.judgeWordCount();
				self.currentScore.firstParagraph = self.judgeFirstParagraph();
				self.currentScore.images = self.judgeImages();
				self.currentScore.links = self.judgeLinks();
				self.currentScore.headings = self.judgeHeadings();
				// Keyword density max 2.5% of text, include total count
				// Flesch Reading Ease (dredd)

				self.updateScoreHtml();
			});
		} else {
			this.updateScoreHtml();
		}
	} else {
		this.score.classList.remove('open');
		this.score.classList.add('disabled');
	}
};

SeoField.prototype.updateScoreHtml = function () {
	this.score.classList.remove('disabled');
	var list = this.score.getElementsByClassName('details-inner')[0];
	list.innerHTML = '';
	for (var key in this.currentScore) {
		if (this.currentScore.hasOwnProperty(key) && this.currentScore[key]) {
			list.innerHTML += '<li>' + this.currentScore[key].reason + '</li>';
		}
	}
};

// CALCULATOR
SeoField.prototype.judgeTitleLength = function () {
	var v = this.titleField.value,
		ret;

	ret = {
		score : (v.length < 40 || v.length > 60) ? SeoField.Levels.BAD : SeoField.Levels.GOOD,
		reason: (v.length < 40) ? SeoField.Reasons.titleLengthFailMin : (v.length > 60) ? SeoField.Reasons.titleLengthFailMax : SeoField.Reasons.titleLengthSuccess
	};
	ret.reason = ret.reason.replace('{l}', v.length);

	return ret;
};

SeoField.prototype.judgeTitleKeyword = function () {
	var ret;

	if (this.titleField.value.indexOf(this.keyword.value) > -1) {
		var w = this.titleField.value.split(' '),
			inFirstHalf = false;

		for (var i = 0; i < w.length/2; i++) {
			if (w[i] == this.keyword.value) {
				inFirstHalf = true;
				break;
			}
		}

		if (inFirstHalf) {
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

	if (this.slugField.value.indexOf(this.keyword.value) > -1) {
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
	if (this.descField.value.indexOf(this.keyword.value) > -1) {
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
	var wc = this.content.textContent.trim().replace(/\s+/gi, ' ').split(' ').length;
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
	if (this.content.querySelector('p').textContent.indexOf(this.keyword.value) > -1) {
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
	var imgs = this.content.getElementsByTagName('img');
	if (imgs) {
		var imgsWithAltKeyword = 0;

		for (var i = 0; i < imgs.length; i++) {
			if (imgs[i].getAttribute('alt') &&
				imgs[i].getAttribute('alt').indexOf(this.keyword.value))
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
	var a = this.content.getElementsByTagName('a');

	if (a) {
		for (var i = 0; i < a.length; i++) {
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
	var headings = this.content.querySelectorAll('h1,h2,h3,h4,h5,h6');

	if (headings) {
		var primary = 0, secondary = 0;

		for (var i = 0; i < headings.length; i++) {
			if (headings[i].textContent.indexOf(this.keyword.value) > -1) {
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

// HELPERS
/**
 * Get Parsed Fields HTML
 *
 * @param {object} fields
 * @param {function} cb
 */
SeoField.getFieldsHTML = function (fields, cb) {
	var data = {};

	for (var i = 0; i < fields.length; i++) {
		/* jshint ignore:start */
		[].slice.call(document.forms[0].elements).forEach(function (el) {
			data[el.getAttribute('name')] = el.value;
		});
		/* jshint ignore:end */
	}

	Craft.postActionRequest('seo/parser?fields=' + fields.join(','), data, function(response) {
		cb(response);
	});
};

/**
 * External URL checker
 * From http://stackoverflow.com/a/9744104/550109
 *
 * @param {string} url
 */
SeoField.isExternalUrl = (function(){
	var domainRe = /https?:\/\/((?:[\w\d]+\.)+[\w\d]{2,})/i;

	return function(url) {
		function domain(url) {
			return domainRe.exec(url)[1];
		}

		return domain(location.href) !== domain(url);
	};
})();

// CONSTS / ENUMS
SeoField.Levels = {
	NONE: 0,
	GOOD: 1,
	OK: 2,
	BAD: 3
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
};