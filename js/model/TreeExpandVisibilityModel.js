/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.TreeExpandVisibilityObserverInterface = function() {};

  /**
   * @param {Object.<bool>} updated
   */
  Drupal.crumbs.TreeExpandVisibilityObserverInterface.prototype.updateVisibleKeys = function(updated) {};

  /**
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
   *
   * @constructor
   * @implements {Drupal.crumbs.TreeExpandObserverInterface}
   */
  Drupal.crumbs.TreeExpandVisibilityModel = function(hierarchy, treeExpandModel) {

    var predictor = new VisibilityPredictor(hierarchy, treeExpandModel);

    /**
     * @type {Object.<bool>}
     */
    var visibleKeys = {};

    /**
     * @type {Array.<Drupal.crumbs.TreeExpandVisibilityObserverInterface>}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.TreeExpandVisibilityObserverInterface} observer
     */
    this.observeExpandedKeys = function(observer) {
      observers.push(observer);
      observer.updateVisibleKeys(visibleKeys);
    };

    /**
     * @param {string} key
     * @param {bool} isVisible
     */
    function setChildrenVisibility(key, isVisible) {
      var updated = {};
      var hasUpdate = false;

      /**
       * @param {string} key
       * @param {bool} isVisible
       */
      function setVisibility(key, isVisible) {
        if (visibleKeys[key] !== isVisible) {
          updated[key] = isVisible;
          visibleKeys[key] = isVisible;
          hasUpdate = true;
        }
      }

      /**
       * @param {string} parentKey
       * @param {bool} parentIsVisible
       */
      function setChildrenVisibilityRecursive(parentKey, parentIsVisible) {
        setVisibility(parentKey, parentIsVisible);
        var parentIsExpanded = treeExpandModel.keyIsExpanded(parentKey);
        for (var childKey in hierarchy.getChildKeys(parentKey)) {
          var childIsVisible = parentIsExpanded && parentIsVisible;
          setChildrenVisibilityRecursive(childKey, childIsVisible);
        }
      }

      setChildrenVisibilityRecursive(key, isVisible);

      if (hasUpdate) {
        for (var i = 0; i < observers.length; ++i) {
          observers[i].updateVisibleKeys(updated);
        }
      }
    }

    /**
     * @param {string} key
     * @param {bool} expanded
     */
    this.updateKeyIsExpanded = function (key, expanded) {
      // Set children visibility.
      if (false === visibleKeys[key]) {
        // If the row itself is not visible, then the visibility of the children
        // won't change either.
        return;
      }
      setChildrenVisibility(key, true);
    };
  };

  /**
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
   *
   * @constructor
   */
  function VisibilityPredictor(hierarchy, treeExpandModel) {

    /**
     * @param {string} key
     * @returns {bool}
     */
    function keyIsVisibleRecursive(key) {
      var parentKey = hierarchy.getParentKey(key);
      if (!parentKey) {
        // Root keys are always expanded.
        return true;
      }
      if (!treeExpandModel.keyIsExpanded(parentKey)) {
        // If the parent is not expanded, hide.
        return false;
      }
      return keyIsVisibleRecursive(parentKey);
    }

    /**
     * @param {string} key
     * @returns {bool}
     */
    this.keyIsVisible = function(key) {
      return keyIsVisibleRecursive(key);
    };
  }

})();
