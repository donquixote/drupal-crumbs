/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @param {jQuery} $table
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   */
  Drupal.crumbs.AdminDropdownTable = function(rows, $table) {

    /**
     * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
     */
    this.onRowActionSetFocusGroup = function(focusGroupModel) {
      jQuery.each(rows, function(key, row) {
        function setFocusGroup() {
          // Set the focus to the group that the clicked row belongs to.
          focusGroupModel.focusGroupWithKey(key);
        }
        row.$tr.hover(setFocusGroup);
        // row.$tr.click(setFocusGroup);
        // row.$select.change(setFocusGroup);
      });
    };

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function (masterValues) {
      for (var key in masterValues) {
        if (rows[key]) {
          rows[key].$select.val(masterValues[key]);
        }
      }
    };

    /**
     * @param {Drupal.crumbs.MasterValueModel} masterValueModel
     */
    this.onSelectActionSetMasterValue = function(masterValueModel) {
      jQuery.each(rows, function(key, row) {
        row.$select.change(function(){
          var value = row.$select.val();
          masterValueModel.setMasterValue(key, value);
        });
      });
    };
  };

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @param {jQuery} $table
   * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
   *
   * @returns {Drupal.crumbs.AdminDropdownTable}
   */
  Drupal.crumbs.AdminDropdownTable.create = function(rows, $table, focusGroupModel, masterValueModel, effectiveValueModel) {

    var adminDropdownTable = new Drupal.crumbs.AdminDropdownTable(rows, $table);

    adminDropdownTable.onRowActionSetFocusGroup(focusGroupModel);

    masterValueModel.observeMasterValues(adminDropdownTable);
    adminDropdownTable.onSelectActionSetMasterValue(masterValueModel);

    return adminDropdownTable;
  };

})();
