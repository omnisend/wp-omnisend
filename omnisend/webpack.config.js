const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	entry: {
		appMarket: './src/app-market',
		connection: './src/connection',
		connected: './src/connected',
	},
	resolve: {
		extensions: ['.js', '.css', '.ts'],
	},
};
