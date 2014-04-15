/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.TreeExpandObserverInterface = function() {};

  /**
   * @param {string} key
   * @param {bool} isExpanded
   */
  Drupal.crumbs.TreeExpandObserverInterface.prototype.updateKeyIsExpanded = function(key, isExpanded) {};

  /**
   * @param {Object.<true>} expansibleKeys
   * @param {Object.<true>} initialExpandedKeys
   *
   * @constructor
   */
  Drupal.crumbs.TreeExpandModel = function(expansibleKeys, initialExpandedKeys) {

    /**
     * Keys where the children are visible.
     * At the beginning, these are only the root keys.
     * Typically, {'*': true}, so that all first-level keys are visible.
     *
     * @type {Object.<bool>}
     */
    var expandedKeys = initialExpandedKeys;

    // Fill up with false.
    for (var key in expansibleKeys) {
      expandedKeys[key] = (true === expandedKeys[key]);
    }

    /**
     * @type {Array.<Drupal.crumbs.TreeExpandObserverInterface>}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.TreeExpandObserverInterface} observer
     */
    this.observeExpandedKeys = function(observer) {
      observers.push(observer);
      for (var key in expandedKeys) {
        observer.updateKeyIsExpanded(key, expandedKeys[key]);
      }
    };

    /**
     * @param {string} key
     */
    this.keyToggleExpanded = function(key) {
      var isExpanded = expandedKeys[key] = !expandedKeys[key];
      for (var i = 0; i < observers.length; ++i) {
        observers[i].updateKeyIsExpanded(key, isExpanded);
      }
    };

    /**
     * @param {string} key
     * @returns {boolean}
     */
    this.keyIsExpanded = function(key) {
      return true === expandedKeys[key];
    };

    /**
     * @param {string} key
     * @returns {boolean}
     */
    this.keyIsExpansible = function(key) {
      return false === !expansibleKeys[key];
    };
  };

  /**
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   */
  Drupal.crumbs.TreeExpandModel.create = function(hierarchy) {
    /** @type {Object.<true>} */
    var expansibleKeys = hierarchy.getKeysWithChildren();
    for (var rootKey in hierarchy.getRootKeys()) {
      delete expansibleKeys[rootKey];
    }
    var initialExpandedKeys = {};
    return new Drupal.crumbs.TreeExpandModel(expansibleKeys, initialExpandedKeys);
  };
})();
