const defaultConfig = require("@wordpress/scripts/config/webpack.config");

module.exports = {
	...defaultConfig,
	entry: {
		appMarket: "./src/app-market",
		connection: "./src/connection",
	},
	resolve: {
		extensions: [".js", ".css", ".ts"],
	},
};
