/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.HasExplicitChildrenObserverInterface = function() {};

  /**
   * @param {Object.<bool>} keysWithExplicitChildren
   */
  Drupal.crumbs.HasExplicitChildrenObserverInterface.prototype.updateHasExplicitChildren = function(keysWithExplicitChildren) {};

  /**
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   * @param {Drupal.crumbs.ExplicityModel} explicityModel
   *
   * @constructor
   * @implements {Drupal.crumbs.ExplicityObserverInterface}
   */
  Drupal.crumbs.HasExplicitChildrenModel = function(hierarchy, explicityModel) {

    /**
     * E.g. {'*': true, 'x.*': false, 'x.y.*': true}
     *
     * @type {Object.<bool>}
     */
    var keysWithExplicitChildren = {};

    /**
     * @type {Drupal.crumbs.HasExplicitChildrenObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {Drupal.crumbs.HasExplicitChildrenObserverInterface} observer
     */
    this.observeHasExplicitChildren = function(observer) {
      observers.push(observer);
      observer.updateHasExplicitChildren(keysWithExplicitChildren);
    };

    /**
     * @param {Object.<bool>} updatedExplicitKeys
     *   This parameter is not really needed because we always recalculate.
     */
    this.updateExplicitKeys = function (updatedExplicitKeys) {
      recalculate();
    };

    /**
     * Recalculate and notify observers.
     */
    function recalculate() {

      /** @type {Object.<bool>} */
      var updated = {};
      var hasUpdate = false;

      function updateHasExplicitChildrenRecursive(parentKey) {
        var hasExplicitChildren = false;
        for (var childKey in hierarchy.getChildKeys(parentKey)) {
          if (updateHasExplicitChildrenRecursive(childKey)) {
            hasExplicitChildren = true;
          }
        }
        if (keysWithExplicitChildren[parentKey] !== hasExplicitChildren) {
          updated[parentKey] = hasExplicitChildren;
          hasUpdate = true;
          keysWithExplicitChildren[parentKey] = hasExplicitChildren;
        }
        return hasExplicitChildren && explicityModel.keyIsExplicit(parentKey)
      }

      for (var rootKey in hierarchy.getRootKeys()) {
        updateHasExplicitChildrenRecursive(rootKey);
      }

      if (hasUpdate) {
        for (var i = 0; i < observers.length; ++i) {
          observers[i].updateHasExplicitChildren(updated);
        }
      }
    }
  };

})();
