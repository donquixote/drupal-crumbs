/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * Widget for row classes management.
   *
   * @param {Drupal.crumbs.RowClassSwitcher} rowClassSwitcher
   *
   * @constructor
   * @implements {Drupal.crumbs.FocusGroupObserverInterface}
   * @implements {Drupal.crumbs.EffectiveValueObserverInterface}
   * @implements {Drupal.crumbs.ExplicityObserverInterface}
   * @implements {Drupal.crumbs.HasExplicitChildrenObserverInterface}
   */
  Drupal.crumbs.AdminTableRows = function(rowClassSwitcher) {

    /**
     * @param {string} key
     */
    this.focusAddKey = function(key) {
      rowClassSwitcher.rowConditionalClass(key, true, 'crumbs-focus-group');
    };

    /**
     * @param {string} key
     */
    this.focusRemoveKey = function(key) {
      rowClassSwitcher.rowConditionalClass(key, false, 'crumbs-focus-group');
    };

    /**
     * @param {string} key
     * @param {int|'disabled'} effectiveValue
     */
    this.updateEffectiveValue = function (key, effectiveValue) {
      var isEnabled = '' + effectiveValue === '' + parseInt(effectiveValue);
      rowClassSwitcher.rowConditionalClass(key, !isEnabled, 'crumbs-rule-disabled');
    };

    /**
     * @param {Object.<bool>} updatedExplicitKeys
     */
    this.updateExplicitKeys = function (updatedExplicitKeys) {
      rowClassSwitcher.rowsConditionalClass(updatedExplicitKeys, 'crumbs-rule-explicit');
    };

    /**
     * @param {Object.<bool>} keysWithExplicitChildren
     */
    this.updateHasExplicitChildren = function(keysWithExplicitChildren) {
      rowClassSwitcher.rowsConditionalClass(keysWithExplicitChildren, 'crumbs-has-explicit-children');
    };

    /**
     * @param {Drupal.crumbs.Hierarchy} hierarchy
     */
    this.addDepthClasses = function (hierarchy) {
      rowClassSwitcher.rowsDynamicClass(function(key) {
        var depth = hierarchy.keyGetDepth(key);
        return 'crumbs-rule-depth-' + depth;
      });
    };
  };

  /**
   * @param {Drupal.crumbs.RowClassSwitcher} rowClassSwitcher
   * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
   * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
   * @param {Drupal.crumbs.ExplicityModel} explicityModel
   * @param {Drupal.crumbs.HasExplicitChildrenModel} hasExplicitChildrenModel
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   *
   * @returns {Drupal.crumbs.AdminTableRows}
   */
  Drupal.crumbs.AdminTableRows.create = function (
    rowClassSwitcher,
    focusGroupModel,
    effectiveValueModel,
    explicityModel,
    hasExplicitChildrenModel,
    hierarchy
  ) {

    var adminTableRows = new Drupal.crumbs.AdminTableRows(rowClassSwitcher);

    focusGroupModel.observeFocusGroup(adminTableRows);
    effectiveValueModel.observeEffectiveValues(adminTableRows);
    explicityModel.observeExplicity(adminTableRows);
    hasExplicitChildrenModel.observeHasExplicitChildren(adminTableRows);

    adminTableRows.addDepthClasses(hierarchy);

    return adminTableRows;
  };

})();
