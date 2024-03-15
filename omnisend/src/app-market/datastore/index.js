import { createReduxStore, register } from '@wordpress/data';
import { DEFAULT_STATE, STORE_NAME, IS_LOADING, SET_ACTIVITY } from './constants';
import { getRandomActivity } from './api';

const actions = {
	setActivity(activity) {
		return {
			type: SET_ACTIVITY,
			payload: { activity }
		};
	},

	setIsLoading(status) {
		return {
			type: IS_LOADING,
			payload: {
				status
			}
		};
	}
};

function reducer(state = DEFAULT_STATE, { type, payload }) {
	switch (type) {
		case IS_LOADING: {
			const { status } = payload;
			return {
				...state,
				isLoading: status
			};
		}
		case SET_ACTIVITY: {
			return {
				...state,
				...payload
			};
		}
	}
	return state;
}

const selectors = {
	getIsLoading(state) {
		return state.isLoading;
	},
	getRandomActivity(state) {
		return state.activity;
	}
};

const resolvers = {
	getRandomActivity() {
		return async ({ dispatch }) => {
			dispatch.setIsLoading(true);
			const activity = await getRandomActivity();
			dispatch.setActivity(activity);
			dispatch.setIsLoading(false);
		};
	}
};

const store = createReduxStore(STORE_NAME, {
	reducer,
	actions,
	selectors,
	resolvers,
	__experimentalUseThunks: true // Fallback for those not using WP 6.0
});

register(store);
