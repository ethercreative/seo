/**
 * Creates a rating element
 *
 * @param {string} level
 * @param {string=} tag
 * @return {Element}
 */
import capitalize from './capitalize';
import t from './createElement';

export default function createRating (level, tag = 'div') {
	const name = capitalize(level);
	
	return t(tag, {
		'class': `seo--light ${level}`,
		'title': name,
	}, name);
}