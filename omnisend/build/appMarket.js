/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/app-market/components/apps-list.js":
/*!************************************************!*\
  !*** ./src/app-market/components/apps-list.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/i18n */ "@wordpress/i18n");
/* harmony import */ var _wordpress_i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _datastore_index__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../datastore/index */ "./src/app-market/datastore/index.js");
/* harmony import */ var _datastore_constants__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../datastore/constants */ "./src/app-market/datastore/constants.js");

/**
 * WordPress dependencies
 */



// eslint-disable-next-line no-unused-vars
/**
 * Internal dependencies
 */


const AppMarket = () => {
  const {
    apps
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    return {
      apps: select(_datastore_constants__WEBPACK_IMPORTED_MODULE_5__.STORE_NAME).getApps()
    };
  });
  console.log(apps);
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "wrap"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Panel, {
    header: (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("loaded gutenberg components")
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelBody, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.PanelRow, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Button, {
    variant: "primary",
    onClick: () => {
      console.log("clicled on the button");
    }
  }, (0,_wordpress_i18n__WEBPACK_IMPORTED_MODULE_1__.__)("SAVE", "pre-publish-checklist"))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AppMarket);

/***/ }),

/***/ "./src/app-market/datastore/api.js":
/*!*****************************************!*\
  !*** ./src/app-market/datastore/api.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getApps: () => (/* binding */ getApps)
/* harmony export */ });
async function getApps() {
  try {
    // Make a GET request to the API
    const response = await fetch("https://www.testEndpoint.com/REST/apps");

    // Check if the response is successful (status code 200)
    if (!response.ok) {
      throw new Error("Failed to fetch apps");
    }

    // Parse the JSON response
    const data = await response.json();

    // Return the apps from the response data
    return data.apps;
  } catch (error) {
    // Handle any errors
    console.error("Error fetching apps:", error);
    return []; // Return an empty array as fallback
  }
}

/***/ }),

/***/ "./src/app-market/datastore/constants.js":
/*!***********************************************!*\
  !*** ./src/app-market/datastore/constants.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DEFAULT_STATE: () => (/* binding */ DEFAULT_STATE),
/* harmony export */   IS_LOADING: () => (/* binding */ IS_LOADING),
/* harmony export */   SET_APPS: () => (/* binding */ SET_APPS),
/* harmony export */   STORE_NAME: () => (/* binding */ STORE_NAME)
/* harmony export */ });
// Constants
const STORE_NAME = "apps-list";
const DEFAULT_STATE = {
  isSaving: false,
  isLoading: true
};
const IS_LOADING = "IS_LOADING";
const SET_APPS = "SET_APPS";

/***/ }),

/***/ "./src/app-market/datastore/index.js":
/*!*******************************************!*\
  !*** ./src/app-market/datastore/index.js ***!
  \*******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _constants__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./constants */ "./src/app-market/datastore/constants.js");
/* harmony import */ var _api__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./api */ "./src/app-market/datastore/api.js");
/**
 * WordPress dependencies
 */




// Define our actions
const actions = {
  setApps(apps) {
    return {
      type: _constants__WEBPACK_IMPORTED_MODULE_1__.SET_APPS,
      payload: {
        apps
      }
    };
  },
  setIsLoading(status) {
    return {
      type: _constants__WEBPACK_IMPORTED_MODULE_1__.IS_LOADING,
      payload: {
        status
      }
    };
  }
};

// Define the reducer
function reducer(state = _constants__WEBPACK_IMPORTED_MODULE_1__.DEFAULT_STATE, {
  type,
  payload
}) {
  switch (type) {
    case _constants__WEBPACK_IMPORTED_MODULE_1__.SET_APPS:
      {
        return {
          ...state,
          ...payload
        };
      }
    case _constants__WEBPACK_IMPORTED_MODULE_1__.IS_LOADING:
      {
        const {
          status
        } = payload;
        return {
          ...state,
          isLoading: status
        };
      }
  }
  return state;
}

// Define some selectors
const selectors = {
  getApps(state) {
    return state.apps;
  },
  getIsLoading(state) {
    return state.isLoading;
  }
};
const resolvers = {
  getApps() {
    return async ({
      dispatch
    }) => {
      dispatch.setIsLoading(true);
      const apps = await (0,_api__WEBPACK_IMPORTED_MODULE_2__.getApps)();
      dispatch.setApps(apps);
      dispatch.setIsLoading(false);
    };
  }
};

// Define and register the store.
const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)(_constants__WEBPACK_IMPORTED_MODULE_1__.STORE_NAME, {
  reducer,
  actions,
  selectors,
  resolvers,
  __experimentalUseThunks: true // Fallback for those not using WP 6.0
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(store);

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

/***/ }),

/***/ "@wordpress/data":
/*!******************************!*\
  !*** external ["wp","data"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["data"];

/***/ }),

/***/ "@wordpress/element":
/*!*********************************!*\
  !*** external ["wp","element"] ***!
  \*********************************/
/***/ ((module) => {

module.exports = window["wp"]["element"];

/***/ }),

/***/ "@wordpress/i18n":
/*!******************************!*\
  !*** external ["wp","i18n"] ***!
  \******************************/
/***/ ((module) => {

module.exports = window["wp"]["i18n"];

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry need to be wrapped in an IIFE because it need to be isolated against other modules in the chunk.
(() => {
/*!*********************************!*\
  !*** ./src/app-market/index.js ***!
  \*********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _components_apps_list__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/apps-list */ "./src/app-market/components/apps-list.js");

/**
 * WordPress dependencies
 */


/**
 * Internal dependencies
 */

// Render the app to the screen.
(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_apps_list__WEBPACK_IMPORTED_MODULE_1__["default"], null), document.getElementById("omnisend-app-market"));
})();

/******/ })()
;
//# sourceMappingURL=appMarket.js.map