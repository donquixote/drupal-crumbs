/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.ExplicityObserverInterface = function() {};

  /**
   * @param {Object.<bool>} updatedExplicitKeys
   */
  Drupal.crumbs.ExplicityObserverInterface.prototype.updateExplicitKeys = function(updatedExplicitKeys) {};

  /**
   * Class to manage a number of keys which can be either enabled, disabled or
   * 'default'.
   * (we assume each key to identify a row in a table)
   *
   * @param {Object.<true>} hardKeys
   *   Keys that cannot inherit from the parent.
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   */
  Drupal.crumbs.ExplicityModel = function(hardKeys) {

    /**
     * E.g. {'*': true, 'x.*': false, 'x.y.*': true}
     *
     * @type {Object.<bool>}
     */
    var explicitKeys = {};

    /**
     * @type {Drupal.crumbs.ExplicityObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.ExplicityObserverInterface} observer
     */
    this.observeExplicity = function(observer) {
      observers.push(observer);
      observer.updateExplicitKeys(explicitKeys);
    };

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function(masterValues) {
      var updated = {};
      var hasUpdate;
      for (var key in masterValues) {
        var isExplicit = hardKeys[key] || 'default' !== masterValues[key];
        if (explicitKeys[key] !== isExplicit) {
          explicitKeys[key] = isExplicit;
          updated[key] = isExplicit;
          hasUpdate = true;
        }
      }
      if (hasUpdate) {
        for (var i = 0; i < observers.length; ++i) {
          observers[i].updateExplicitKeys(updated)
        }
      }
    };

    /**
     * @param {string} key
     * @returns {boolean}
     */
    this.keyIsExplicit = function (key) {
      return true === explicitKeys[key];
    };
  };

})();
