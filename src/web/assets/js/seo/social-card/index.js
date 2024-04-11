const TEMPLATE = document.createElement('template');
TEMPLATE.innerHTML = `
	<style>
		:host {
			position: relative;
		
			display: flex;
			align-items: center;
			justify-content: center;
			flex-direction: column;
			padding: var(--m) var(--m) var(--l);
			border: 1px solid var(--gray-200) !important;
			background: var(--gray-050);
			border-radius: 2px;
			box-shadow: 0 2px 6px 0 rgba(35,36,46,.08) !important;
		}
		
		.logo {
			display: flex;
			justify-content: flex-end;
			width: 100%;
			max-width: 504px;
			margin-bottom: var(--m);
			
			& ::slotted(svg) {
				width: 32px;
				height: 32px;
			}
		}
		
		.wrap {
			width: 100%;
			max-width: 504px;
			
			box-shadow: 0 2px 4px 0 rgba(0,0,0,.1);
			background: #fff;
			border: 1px solid var(--gray-200);
			border-radius: 4px;
		}
		
		.preview {
			display: flex;
			align-items: center;
			justify-content: center;
			width: 100%;
			aspect-ratio: 1.91;
			
			appearance: none;
			background: none;
			border: none;
			cursor: pointer;
			
			&:focus {
				outline: none;
				box-shadow: var(--focus-ring);
			}
		}
		
		.prompt {
			display: inline-block;
			max-width: 332px;
			
			color: var(--gray-300);
			text-align: center;
			opacity: 0.6;
		
			& svg {
				width: 59px;
			}
			
			& path {
				fill: var(--gray-300);
			}
			
			& strong {
				font-size: 24px;
			}
			
			& p:last-child {
				margin-bottom: 0;
				font-size: 14px;
				line-height: 1.7em;
			}
		}
		
		.copy {
			display: flex;
			flex-direction: column;
			padding: var(--s);
			border-top: 1px solid var(--gray-200);
			
			& ::slotted(textarea) {
				font-size: 14px !important;
			
				appearance: none;
				background: none;
				border: none;
				resize: none !important;
				overflow: hidden;
				
				&:focus {
					outline: none;
					box-shadow: var(--focus-ring);
				}
				
				&::placeholder {
					color: red !important; // fixme
				}
			}
		}
		
		slot[name="title"]::slotted(textarea) {
			color: var(--gray-500);
			font-weight: bold !important;
		}
		
		slot[name="description"]::slotted(textarea) {
			color: var(--gray-300);
		}
	</style>
	<div class="logo"><slot name="logo"></slot></div>
	<div class="wrap">
		<button class="preview" type="button">
			<div class="prompt">
				<svg viewBox="0 0 59 38">
					<path d="M48.21 16c0-.17.03-.32.03-.48C48.24 6.94 41.42 0 33 0a15.19 15.19 0 0 0-13.73 8.84 7.79 7.79 0 0 0-3.53-.86 7.9 7.9 0 0 0-7.75 6.67A11.95 11.95 0 0 0 0 25.99 11.9 11.9 0 0 0 11.79 38h13.5V27.44h-6.35L29.5 16.4l10.56 11.03h-6.35V38h14.53A10.9 10.9 0 0 0 59 27c0-6.06-4.83-10.99-10.79-11Z"/>
				</svg>
				<p><strong>Select an Image</strong></p>
				<p>If you don't select an image, the default (specified in the SEO settings) will be used if available.</p>
			</div>
		</button>
		<slot name="image"></slot>
		<div class="copy">
			<slot name="title"></slot>
			<slot name="description"></slot>
		</div>
	</div>
`;

class SeoSocialCard extends HTMLElement {

	constructor () {
		super();

		this.attachShadow({ mode: 'open' });
		this.shadowRoot.append(TEMPLATE.content.cloneNode(true));
	};

}

customElements.define('seo-social-card', SeoSocialCard);
