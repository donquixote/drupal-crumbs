/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.InheritanceObserverInterface = function() {};

  /**
   * @param {Object.<string>} updatedEffectiveParentKeys
   */
  Drupal.crumbs.InheritanceObserverInterface.prototype.updateEffectiveParentKeys = function(updatedEffectiveParentKeys) {};

  /**
   * Class to determine which keys have any ancestors with overridden values.
   *
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   *   The complete hierarchy of keys, with '*' as the root.
   * @param {Drupal.crumbs.ExplicityModel} explicityModel
   *
   * @constructor
   * @implements {Drupal.crumbs.ExplicityObserverInterface}
   */
  Drupal.crumbs.InheritanceModel = function(hierarchy, explicityModel) {

    /**
     * For each key, which (other) key does this inherit from.
     * E.g. {'x.y.z.*': 'x.*', 'x.y.*': 'x.*', 'x.*': 'x.*'}
     *
     * @type {Object.<string>}
     */
    var effectiveParentKeys = {};

    /**
     * @type {Drupal.crumbs.InheritanceObserverInterface[]}
     */
    var observers = [];


    function recalculateAllParents() {

      var updates = {};

      /**
       * A key has changed from explicit to inherit or vice versa, and thus the
       * status for all parents needs to be updated.
       *
       * @param {string} key
       * @param {string} effectiveParentKey
       */
      function recalculateKeyParents(key, effectiveParentKey) {
        if (effectiveParentKeys[key] !== effectiveParentKey) {
          updates[key] = effectiveParentKey;
          effectiveParentKeys[key] = effectiveParentKey;
        }
        for (var childKey in hierarchy.getChildKeys(key)) {
          if (explicityModel.keyIsExplicit(childKey)) {
            recalculateKeyParents(childKey, childKey);
          }
          else {
            recalculateKeyParents(childKey, effectiveParentKey);
          }
        }
      }

      for (var rootKey in hierarchy.getRootKeys()) {
        recalculateKeyParents(rootKey, rootKey);
      }

      for (var i = 0; i < observers.length; ++i) {
        observers[i].updateEffectiveParentKeys(updates);
      }
    }

    /**
     * @param {Drupal.crumbs.InheritanceObserverInterface} observer
     */
    this.observeEffectiveParentKeys = function(observer) {
      observers.push(observer);
      observer.updateEffectiveParentKeys(effectiveParentKeys);
    };

    /**
     * @param {Object.<bool>} updatedExplicitKeys
     */
    this.updateExplicitKeys = function (updatedExplicitKeys) {
      recalculateAllParents();
    };

    /**
     * @param {string} key
     * @returns {string}
     */
    this.getEffectiveParentKey = function(key) {
      return effectiveParentKeys[key];
    };

    /**
     * Gets all direct child keys that inherit from the given key.
     *
     * @param {string} key
     * @returns {Object.<true>}
     */
    this.getEffectiveChildKeys = function(key) {
      var effectiveChildKeys = {};
      for (var childKey in hierarchy.getChildKeys(key)) {
        if (!explicityModel.keyIsExplicit(childKey)) {
          effectiveChildKeys[childKey] = true;
        }
      }
      return effectiveChildKeys;
    };

    /**
     * @param {string} key
     * @returns {boolean}
     */
    this.keyIsExplicit = function (key) {
      return explicityModel.keyIsExplicit(key);
    };
  };

})();
