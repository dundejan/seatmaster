module.exports = {
	extends: ['eslint:recommended', 'plugin:react/recommended'],
	parserOptions: {
		ecmaVersion: 2020,
		sourceType: 'module',
		ecmaFeatures: {
			jsx: true
		}
	},
	env: {
		browser: true,
		es6: true,
		node: true
	},
	rules: {
		"no-console": 0,
		"no-unused-vars": 0
	},
	settings: {
		react: {
			version: "detect"
		}
	}
};