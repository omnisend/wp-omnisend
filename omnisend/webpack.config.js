const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );

module.exports = {
	...defaultConfig,
	entry: {
		appMarket: './src/app-market',
		connected: './src/connected',
		notices: './src/notices',
	},
	resolve: {
		extensions: [ '.js', '.css', '.ts' ]
	}
};
