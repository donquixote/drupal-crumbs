/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.MasterValueObserverInterface = function() {};

  /**
   * @param {Object.<int|'disabled'|'default'>} masterValues
   *   The complete set of master values.
   */
  Drupal.crumbs.MasterValueObserverInterface.prototype.updateMasterValues = function(masterValues) {};

  /**
   * @param {Object.<int|'disabled'|'default'>} initialValues
   *   E.g. {'x.y.*': 4, 'x.y.z.*': 'default'}
   * @constructor
   */
  Drupal.crumbs.MasterValueModel = function(initialValues) {

    /**
     * @type {Object.<int|'disabled'|'default'>}
     */
    var masterValues = initialValues;

    /**
     * @type {Drupal.crumbs.MasterValueObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.MasterValueObserverInterface} observer
     */
    this.observeMasterValues = function(observer) {
      observers.push(observer);
      observer.updateMasterValues(masterValues);
    };

    /**
     * @param {string} key
     * @param {int|'disabled'|'default'} value
     */
    this.setMasterValue = function(key, value) {
      if (masterValues[key] === value) {
        // Nothing has changed.
        return;
      }
      masterValues[key] = value;
      normalizeMasterValues(masterValues);
      for (var i = 0; i < observers.length; ++i) {
        observers[i].updateMasterValues(masterValues);
      }
    };

    /**
     * @param {Object.<int|'disabled'|'default'>} values
     */
    this.setMasterValues = function(values) {
      var hasUpdate;
      for (var key in values) {
        if (masterValues[key] === values[key]) {
          continue;
        }
        hasUpdate = true;
        masterValues[key] = values[key];
      }
      // Only call observers after all values have been updated.
      if (hasUpdate) {
        normalizeMasterValues(masterValues);
        for (var i = 0; i < observers.length; ++i) {
          observers[i].updateMasterValues(masterValues);
        }
      }
    };

    /**
     * @param {string} key
     *
     * @returns {int|'disabled'|'default'}
     */
    this.getMasterValue = function(key) {
      return masterValues[key];
    };
  };

  /**
   * Normalize the numeric values.
   *
   * @param {Object.<int|'disabled'|'default'>} masterValues
   */
  function normalizeMasterValues(masterValues) {
    /** @type {Array.<string>} */
    var keysWithWeight = [];
    for (var key in masterValues) {
      var value = masterValues[key];
      if ('' + value === '' + parseInt(value)) {
        keysWithWeight.push(key);
      }
    }
    keysWithWeight.sort(function(key0, key1) {
      if (masterValues[key0] < masterValues[key1]) {
        return -1;
      }
      else if (masterValues[key0] > masterValues[key1]) {
        return 1;
      }
      else {
        return 0;
      }
    });
    for (var i = 0; i < keysWithWeight.length; ++i) {
      masterValues[keysWithWeight[i]] = i + 1;
    }
  }
})();
