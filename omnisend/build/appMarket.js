/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/app-market/components/apps-list-layout.js":
/*!*******************************************************!*\
  !*** ./src/app-market/components/apps-list-layout.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _styles_styles__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../styles/styles */ "./src/app-market/styles/styles.css");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! @wordpress/data */ "@wordpress/data");
/* harmony import */ var _wordpress_data__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(_wordpress_data__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _apps_list__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./apps-list */ "./src/app-market/components/apps-list.js");
/* harmony import */ var _apps_list_notice__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./apps-list-notice */ "./src/app-market/components/apps-list-notice.js");
/* harmony import */ var _datastore_index__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../datastore/index */ "./src/app-market/datastore/index.js");
/* harmony import */ var _datastore_constants__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ../datastore/constants */ "./src/app-market/datastore/constants.js");








const AppsListLayout = () => {
  const {
    apps,
    categories,
    isLoading
  } = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_3__.useSelect)(select => {
    return {
      apps: select(_datastore_constants__WEBPACK_IMPORTED_MODULE_7__.STORE_NAME).getApps(),
      categories: select(_datastore_constants__WEBPACK_IMPORTED_MODULE_7__.STORE_NAME).getCategories(),
      isLoading: select(_datastore_constants__WEBPACK_IMPORTED_MODULE_7__.STORE_NAME).getIsLoading()
    };
  });
  if (isLoading) {
    return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Spinner, null);
  }
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.Flex, {
    className: "omnisend-apps-list-page-layout",
    justify: "center",
    style: {
      margin: "40px 0"
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalSpacer, {
    marginBottom: 10
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_apps_list_notice__WEBPACK_IMPORTED_MODULE_5__["default"], null)), categories.map(category => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: category.id
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_2__.__experimentalSpacer, {
    marginBottom: 15
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_apps_list__WEBPACK_IMPORTED_MODULE_4__["default"], {
    apps: apps.filter(app => app.category_id === category.id),
    categoryName: category.name,
    categoryDescription: category.description
  }))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AppsListLayout);

/***/ }),

/***/ "./src/app-market/components/apps-list-notice.js":
/*!*******************************************************!*\
  !*** ./src/app-market/components/apps-list-notice.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const AppsListNotice = () => {
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, {
    isBorderless: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, {
    isBorderless: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    direction: "column"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalHeading, {
    level: 1
  }, "Omnisend Add-Ons"), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalText, {
    size: 14,
    style: {
      maxWidth: "360px"
    }
  }, "You can expand the possibilities of Omnisend by integrating it with additional add-ons."))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AppsListNotice);

/***/ }),

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
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const AppsList = ({
  apps,
  categoryName,
  categoryDescription
}) => {
  const navigateToPluginPage = url => {
    window.open(url, "_blank").focus();
  };
  return (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalSpacer, {
    marginBottom: 6
  }, categoryName && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalHeading, null, categoryName), categoryDescription && (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalText, null, categoryDescription)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    gap: 6,
    wrap: true,
    justify: "start",
    style: {
      margin: "auto",
      maxWidth: "950px"
    }
  }, apps && apps.map(app => (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.FlexItem, {
    key: app.slug
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, {
    size: "medium",
    isBorderless: true,
    backgroundSize: 50,
    style: {
      maxWidth: "300px"
    }
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardHeader, {
    isBorderless: "true"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    direction: "column"
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    src: app.logo,
    style: {
      width: "40px",
      height: "40px"
    }
  }), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalHeading, {
    level: 4
  }, app.name), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalText, {
    size: 12
  }, "by ", app.created_by))), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, null, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.__experimentalText, {
    size: 14
  }, app.description)), (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardFooter, {
    isBorderless: true
  }, (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
    variant: "primary",
    onClick: () => navigateToPluginPage(app.url)
  }, "Add this add-on")))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AppsList);

/***/ }),

/***/ "./src/app-market/datastore/api.js":
/*!*****************************************!*\
  !*** ./src/app-market/datastore/api.js ***!
  \*****************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getPluginData: () => (/* binding */ getPluginData)
/* harmony export */ });
async function getPluginData() {
  try {
    const response = await fetch("https://omnisend.github.io/wp-omnisend/plugins.json");
    if (!response.ok) {
      throw new Error("Failed to fetch plugins data");
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching plugins data", error);
    return {};
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
/* harmony export */   SET_CATEGORIES: () => (/* binding */ SET_CATEGORIES),
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
const SET_CATEGORIES = "SET_CATEGORIES";

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



const actions = {
  setIsLoading(status) {
    return {
      type: _constants__WEBPACK_IMPORTED_MODULE_1__.IS_LOADING,
      payload: {
        status
      }
    };
  },
  setApps(apps) {
    return {
      type: _constants__WEBPACK_IMPORTED_MODULE_1__.SET_APPS,
      payload: {
        apps
      }
    };
  },
  setCategories(categories) {
    return {
      type: _constants__WEBPACK_IMPORTED_MODULE_1__.SET_CATEGORIES,
      payload: {
        categories
      }
    };
  }
};
function reducer(state = _constants__WEBPACK_IMPORTED_MODULE_1__.DEFAULT_STATE, {
  type,
  payload
}) {
  switch (type) {
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
    case _constants__WEBPACK_IMPORTED_MODULE_1__.SET_APPS:
      {
        const {
          apps
        } = payload;
        return {
          ...state,
          apps
        };
      }
    case _constants__WEBPACK_IMPORTED_MODULE_1__.SET_CATEGORIES:
      {
        const {
          categories
        } = payload;
        return {
          ...state,
          categories
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
  }
};
const resolvers = {
  getApps() {
    return async ({
      dispatch
    }) => {
      dispatch.setIsLoading(true);
      const data = await (0,_api__WEBPACK_IMPORTED_MODULE_2__.getPluginData)();
      dispatch.setApps(data.plugins);
      dispatch.setCategories(data.categories);
      dispatch.setIsLoading(false);
    };
  }
};
const store = (0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.createReduxStore)(_constants__WEBPACK_IMPORTED_MODULE_1__.STORE_NAME, {
  reducer,
  actions,
  selectors,
  resolvers,
  __experimentalUseThunks: true // Fallback for those not using WP 6.0
});
(0,_wordpress_data__WEBPACK_IMPORTED_MODULE_0__.register)(store);

/***/ }),

/***/ "./src/app-market/styles/styles.css":
/*!******************************************!*\
  !*** ./src/app-market/styles/styles.css ***!
  \******************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


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
/* harmony import */ var _components_apps_list_layout__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./components/apps-list-layout */ "./src/app-market/components/apps-list-layout.js");



(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.render)((0,_wordpress_element__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_apps_list_layout__WEBPACK_IMPORTED_MODULE_1__["default"], null), document.getElementById("omnisend-app-market"));
})();

/******/ })()
;
//# sourceMappingURL=appMarket.js.map