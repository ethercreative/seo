// TODO: Make all public facing text translatable

export const SEO_RATING = {
	NONE: 'neutral',
	GOOD: 'good',
	AVERAGE: 'average',
	POOR: 'poor',
};

export const SEO_RATING_LABEL = {
	neutral: 'Not yet rated',
	good: 'Looks good',
	average: 'Room for improvement',
	poor: 'Needs work',
};

export const SEO_REASONS = {
	noContent: 'You don\'t have any content, adding some would be a good start!',
	
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
	
	imagesFail: 'Less than a third of the images have alt tags containing the keyword, try adding it to more images.',
	imagesFailMax: 'Most of the images have alt tags containing the keyword. This may be too many and can lead to a poor experience for visually impaired users.',
	imagesOk: 'More than half of the images have alt tags containing the keyword. Ensure the alt tags are contextual to the content, and not stuffed with keywords.',
	imagesSuccess: 'An acceptable number of images have alt tags containing the keyword.',
	
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