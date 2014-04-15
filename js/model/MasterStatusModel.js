/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.MasterStatusObserverInterface = function() {};

  /**
   * @param {string} key
   *   E.g. 'x.y.*'
   * @param {'enabled'|'disabled'|'default'} status
   *   The updated master value for the given key.
   */
  Drupal.crumbs.MasterStatusObserverInterface.prototype.updateMasterStatus = function(key, status) {};

  /**
   * Class to manage a number of keys which can be either enabled, disabled or
   * 'default'.
   * (we assume each key to identify a row in a table)
   *
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   */
  Drupal.crumbs.MasterStatusModel = function(masterValueModel, effectiveValueModel) {

    /**
     * E.g. {'x.y.*': 'enabled', 'x.*': 'default', '*': 'enabled'}
     *
     * @type {{}}
     */
    var statuses = {};

    /**
     * @type {Drupal.crumbs.MasterStatusObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.MasterStatusObserverInterface} observer
     */
    this.observeMasterStatuses = function(observer) {
      observers.push(observer);
      for (var key in statuses) {
        observer.updateMasterStatus(key, statuses[key]);
      }
    };

    /**
     * @param {string} key
     *   E.g. 'x.y.*'
     * @param {'enabled'|'disabled'|'default'} status
     */
    this.setMasterStatus = function(key, status) {
      validateStatus(status);
      // Do not notify observers or anything, this will happen when
      // masterValueModel talks back.
      var value = statusToValue(status, key);
      masterValueModel.setMasterValue(key, value);
    };

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function(masterValues) {
      var updated = {};
      var hasUpdate;
      for (var key in masterValues) {
        var status = valueToStatus(masterValues[key]);
        if (statuses[key] !== status) {
          updated[key] = statuses[key] = status;
          hasUpdate = true;
        }
      }
      if (hasUpdate) {
        for (var updatedKey in updated) {
          for (var i = 0; i < observers.length; ++i) {
            observers[i].updateMasterStatus(updatedKey, updated[updatedKey]);
          }
        }
      }
    };

    /**
     * @param {string} key
     *   E.g. 'x.y.*'
     * @param {'enabled'|'disabled'|'default'} status
     */
    function statusToValue(status, key) {
      switch (status) {
        case 'enabled':
          var effectiveValue = effectiveValueModel.keyGetEffectiveValue(key);
          return getEffectiveWeight(effectiveValue);
        case 'disabled':
          return 'disabled';
        case 'default':
          return 'default';
      }
    }
  };

  /**
   * @param {int|'disabled'|'default'|'disabled'} value
   * @returns {'enabled'|'disabled'|'default'}
   */
  function valueToStatus(value) {
    if ('' + parseInt(value) === '' + value) {
      return 'enabled';
    }
    else if (value === 'disabled') {
      return 'disabled';
    }
    else {
      return 'default';
    }
  }

  /**
   * @param {string|*} status
   */
  function validateStatus(status) {
    switch (status) {
      case 'enabled':
      case 'disabled':
      case 'default':
        break;
      default:
        throw 'Illegal status: ' + status;
    }
  }

  function getEffectiveWeight(effectiveValue) {
    if ('' + effectiveValue === '' + parseInt(effectiveValue)) {
      return parseInt(effectiveValue);
    }
    return 9999;
  }
})();
