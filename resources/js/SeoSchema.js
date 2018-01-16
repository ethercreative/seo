const Vue = window.Vue || {}
	, Craft = window.Craft || {}
	, $ = window.$ || {};
Vue.options.delimiters = ["${", "}"];

// Helpers
// =========================================================================

function get (url, data, callback) {
	$.ajax({
		url: Craft.actionUrl + "/seo/" + url,
		method: "POST",
		data: {
			[Craft.csrfTokenName]: Craft.csrfTokenValue,
			...data,
		},
		xhrFields: { withCredentials: true },
		success: data => {
			callback && callback(data);
		},
		error: err => {
			Craft.cp.displayError(`<strong>SEO:</strong> ${err.message}`);
		},
	});
}

// Components
// =========================================================================

Vue.component("schema-lane", {
	props: ["laneSchema", "onClick", "selectedSchemaIds"],
	template: "#seoSchemaLane",
});

// SeoSchema
// =========================================================================

function SeoSchema (topLevelSchema, thingProperties) {
	new Vue({
		el: "#seoSchemaWrap",
		data: {
			ready: false,
			schema: [topLevelSchema],
			selectedSchemaIds: ["http://schema.org/Thing"],
			selectedProperties: [thingProperties],
		},
		created: function () {
			this.ready = true;
		},
		methods: {
			
			// Events
			// =================================================================
			
			onSchemaClick: function (index) {
				return (e, schemaId) => {
					e.preventDefault();
					
					if (schemaId.indexOf("Thing") > -1) {
						this.schema = [topLevelSchema];
						this.selectedSchemaIds = [schemaId];
						this.selectedProperties = [thingProperties];
						return;
					}
					
					this.getChildren(schemaId, ({ children }) => {
						const nextSchema = []
							, nextIds = [];
						
						let i = index;
						while (i--) {
							nextIds[i] = this.selectedSchemaIds[i];
							nextSchema[i] = this.schema[i];
						}
						
						nextIds[index] = schemaId;
						nextSchema[index] = children;
						this.selectedSchemaIds = nextIds;
						this.schema = nextSchema;
						
						this.getProperties(schemaId, ({ properties }) => {
							const nextProperties = [];
							
							let i = index;
							while (i--)
								nextProperties[i] = this.selectedProperties[i];
							
							nextProperties[index] = properties;
							this.selectedProperties = nextProperties;
						});
					});
				};
			},
			
			// API
			// =================================================================
			
			getChildren: function (schemaId, callback) {
				get("schema/getChildren", { schemaId }, callback);
			},
			
			getProperties: function (schemaId, callback) {
				get("schema/getProperties", { schemaId }, callback);
			},
			
		},
		
		computed: {
			getSelectedSchemaIdsReversed: function () {
				return [...this.selectedSchemaIds].reverse();
			},
			getSelectedPropertiesReversed: function () {
				return [...this.selectedProperties].reverse();
			},
		},
	});
}

window.SeoSchema = SeoSchema;