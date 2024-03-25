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
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _apps_list__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./apps-list */ "./src/app-market/components/apps-list.js");
/* harmony import */ var _apps_list_notice__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./apps-list-notice */ "./src/app-market/components/apps-list-notice.js");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_4___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_4__);
/* harmony import */ var _static_plugins_data_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../static/plugins-data.js */ "./src/app-market/static/plugins-data.js");






const AppsListLayout = () => {
  const [apps, setApps] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)([]);
  const [categories, setCategories] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)([]);
  const [isLoading, setIsLoading] = (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useState)(true);
  (0,_wordpress_element__WEBPACK_IMPORTED_MODULE_4__.useEffect)(() => {
    const getApps = async () => {
      const response = await fetch('https://omnisend.github.io/wp-omnisend/plugins.json');
      if (!response.ok) {
        return _static_plugins_data_js__WEBPACK_IMPORTED_MODULE_5__.PLUGINS_DATA;
      }
      return response.json();
    };
    getApps().then(res => {
      setApps(res.plugins);
      setCategories(res.categories);
      setIsLoading(false);
    }).catch(() => {
      // eslint-disable-next-line no-console
      console.error('Failed to load apps');
    });
  }, []);
  if (isLoading) {
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Spinner, null);
  }
  if (!apps.length && !categories.length) {
    return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, "Failed to load");
  }
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    className: "omnisend-page-layout",
    justify: "center"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-spacing-mb-10"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_apps_list_notice__WEBPACK_IMPORTED_MODULE_3__["default"], null)), categories.map(category => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    key: category.id
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-spacing-mb-15"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_apps_list__WEBPACK_IMPORTED_MODULE_2__["default"], {
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
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const AppsListNotice = () => {
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, {
    isBorderless: true,
    size: "large"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, {
    isBorderless: true
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    direction: "column"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-wp-h1"
  }, "Omnisend Add-Ons"), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-apps-list-notice-text omnisend-wp-text-body"
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
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/components */ "@wordpress/components");
/* harmony import */ var _wordpress_components__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__);


const AppsList = ({
  apps,
  categoryName,
  categoryDescription
}) => {
  const navigateToPluginPage = url => {
    window.open(url, '_blank').focus();
  };
  return (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(react__WEBPACK_IMPORTED_MODULE_0__.Fragment, null, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-spacing-mb-8"
  }, categoryName && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-wp-h2"
  }, categoryName), categoryDescription && (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", null, categoryDescription)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    gap: 6,
    wrap: true,
    justify: "start",
    className: "omnisend-apps-list-container"
  }, apps && apps.map(app => (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Card, {
    key: app.slug,
    size: 'medium',
    isBorderless: true,
    backgroundSize: 50,
    className: "omnisend-apps-list-card"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    direction: "column"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardHeader, {
    isBorderless: "true"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Flex, {
    direction: "column"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("img", {
    alt: app.name,
    className: "omnisend-apps-list-card-logo",
    src: app.logo
  }), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-wp-h4"
  }, app.name), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-wp-text-mini"
  }, "by ", app.created_by))), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardBody, {
    className: "omnisend-apps-list-card-description-container"
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)("div", {
    className: "omnisend-wp-text-body"
  }, app.description)), (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.CardFooter, {
    isBorderless: true
  }, (0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_wordpress_components__WEBPACK_IMPORTED_MODULE_1__.Button, {
    variant: "primary",
    onClick: () => navigateToPluginPage(app.url)
  }, "Add this add-on")))))));
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AppsList);

/***/ }),

/***/ "./src/app-market/static/plugins-data.js":
/*!***********************************************!*\
  !*** ./src/app-market/static/plugins-data.js ***!
  \***********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   PLUGINS_DATA: () => (/* binding */ PLUGINS_DATA)
/* harmony export */ });
const PLUGINS_DATA = {
  categories: [{
    id: 'form',
    name: 'Form add-ons',
    description: 'Sends form data and contact information automatically to Omnisend'
  }, {
    id: 'other',
    name: 'Other add-ons',
    description: ''
  }],
  plugins: [{
    slug: 'omnisend-for-gravity-forms-add-on',
    name: 'Omnisend for Gravity Forms Add-On',
    created_by: 'Omnisend',
    category_id: 'form',
    description: 'Sends form data and contact information to Omnisend automatically from Gravity Forms',
    logo: 'https://appmarket-media.omnisend.com/gravity_forms/gravity_forms_logo.svg',
    url: 'https://wordpress.org/plugins/omnisend-for-gravity-forms-add-on/'
  }, {
    slug: 'omnisend-for-contact-form-7',
    name: 'Omnisend for Contact Form 7 Add-On',
    created_by: 'Omnisend',
    category_id: 'form',
    description: 'Sends form data and contact information to Omnisend automatically from Contact Form 7',
    logo: 'https://appmarket-media.omnisend.com/contact_form_7/contact_form_7_logo.svg',
    url: 'https://wordpress.org/plugins/omnisend-for-contact-form-7/'
  }, {
    slug: 'omnisend-for-ninja-forms-add-on',
    name: 'Omnisend for Ninja Forms Add-On',
    created_by: 'Omnisend',
    category_id: 'form',
    description: 'Sends form data and contact information to Omnisend automatically from Ninja Forms',
    logo: 'https://appmarket-media.omnisend.com/ninja_forms/ninja_forms_logo.png',
    url: 'https://wordpress.org/plugins/omnisend-for-ninja-forms-add-on/'
  }, {
    slug: 'omnisend-for-formidable-forms-add-on',
    name: 'Omnisend for Formidable Forms Add-On',
    created_by: 'Omnisend',
    category_id: 'form',
    description: 'Sends form data and contact information to Omnisend automatically from Formidable Forms',
    logo: 'https://appmarket-media.omnisend.com/formidable_forms/formidable_forms_logo.svg',
    url: 'https://wordpress.org/plugins/omnisend-for-formidable-forms-add-on/'
  }, {
    slug: 'wp-fusion-lite',
    name: 'WP Fusion Lite – Marketing Automation and CRM Integration for WordPress',
    created_by: 'Very Good Plugins',
    category_id: 'other',
    description: 'WP Fusion Lite synchronizes your WordPress users with contact records in your CRM or marketing automation system.',
    logo: 'https://appmarket-media.omnisend.com/wp_fusion/wp_fusion_logo.svg',
    url: 'https://wordpress.org/plugins/wp-fusion-lite/'
  }, {
    slug: 'ws-form',
    name: 'WS Form PRO',
    created_by: 'WS Form',
    category_id: 'form',
    description: 'Sends form data and contact information to Omnisend automatically from wsform',
    logo: 'https://appmarket-media.omnisend.com/ws_form/ws_form_logo.png',
    url: 'https://wsform.com/knowledgebase/omnisend/'
  }]
};

/***/ }),

/***/ "react":
/*!************************!*\
  !*** external "React" ***!
  \************************/
/***/ ((module) => {

module.exports = window["React"];

/***/ }),

/***/ "@wordpress/components":
/*!************************************!*\
  !*** external ["wp","components"] ***!
  \************************************/
/***/ ((module) => {

module.exports = window["wp"]["components"];

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
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! react */ "react");
/* harmony import */ var react__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(react__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! @wordpress/element */ "@wordpress/element");
/* harmony import */ var _wordpress_element__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(_wordpress_element__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var _components_apps_list_layout__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./components/apps-list-layout */ "./src/app-market/components/apps-list-layout.js");



(0,_wordpress_element__WEBPACK_IMPORTED_MODULE_1__.render)((0,react__WEBPACK_IMPORTED_MODULE_0__.createElement)(_components_apps_list_layout__WEBPACK_IMPORTED_MODULE_2__["default"], null), document.getElementById('omnisend-app-market'));
})();

/******/ })()
;
//# sourceMappingURL=appMarket.js.map