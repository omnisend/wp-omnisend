import { createReduxStore, register } from "@wordpress/data";
import {
	DEFAULT_STATE,
	STORE_NAME,
	IS_LOADING,
	SET_APPS,
	SET_CATEGORIES,
} from "./constants";
import { getPluginData } from "./api";

const actions = {
	setIsLoading(status) {
		return {
			type: IS_LOADING,
			payload: {
				status,
			},
		};
	},
	setApps(apps) {
		return {
			type: SET_APPS,
			payload: { apps },
		};
	},
	setCategories(categories) {
		return {
			type: SET_CATEGORIES,
			payload: { categories },
		};
	},
};

function reducer(state = DEFAULT_STATE, { type, payload }) {
	switch (type) {
		case IS_LOADING: {
			const { status } = payload;
			return {
				...state,
				isLoading: status,
			};
		}
		case SET_APPS: {
			const { apps } = payload;
			return {
				...state,
				apps,
			};
		}

		case SET_CATEGORIES: {
			const { categories } = payload;
			return {
				...state,
				categories,
			};
		}
	}
	return state;
}

const selectors = {
	getIsLoading(state) {
		return state.isLoading;
	},
	getApps(state) {
		return state.apps;
	},
	getCategories(state) {
		return state.categories;
	},
};

const resolvers = {
	getApps() {
		return async ({ dispatch }) => {
			dispatch.setIsLoading(true);
			const data = await getPluginData();

			dispatch.setApps(data.plugins);
			dispatch.setCategories(data.categories);
			dispatch.setIsLoading(false);
		};
	},
};

const store = createReduxStore(STORE_NAME, {
	reducer,
	actions,
	selectors,
	resolvers,
	__experimentalUseThunks: true, // Fallback for those not using WP 6.0
});

register(store);
