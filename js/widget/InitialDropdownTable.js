/*global jQuery, document, Drupal */

(function () {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * Wraps the original dropdown table before any client-side modifications are
   * applied. This is used ot read out extract values and structural information
   * from the original table structure. Also contains some starter methods for
   * modifications, the implementation of which is usually in a separate widget
   * class.
   *
   * @param {jQuery} $table
   * @constructor
   */
  Drupal.crumbs.InitialDropdownTable = function($table) {

    /**
     * @type {string}
     */
    var rootId = 'crumbs-rule-3389dae361af79b04c9c8e7057f60cc6';

    /**
     * @type {Object.<Drupal.crumbs.dropdowns.RowInfo>}
     */
    var rows = extractRows($table, rootId);

    /**
     * @type {Object.<jQuery>}
     *   The <tr> elements.
     */
    var trRows = rowsGetTrRows(rows);

    /**
     * @type {Object.<bool>}
     *   E.g. {'x.y.*': true}
     */
    var keys = rowsExtractKeys(rows);

    /**
     * @type {Object.<bool>}
     *   E.g. {'*': true}
     */
    var rootKeys = rowsExtractRootKeys(rows);

    /**
     * Gets all keys.
     *
     * @returns {Object.<bool>}
     *   E.g. {'x.y.*': true}
     */
    this.getKeys = function() {
      return keys;
    };

    /**
     * Gets the root keys.
     *
     * @returns {Object.<bool>}
     *   E.g. {'*': true}
     */
    this.getRootKeys = function() {
      return rootKeys;
    };

    /**
     * @returns {Object.<int|'disabled'|'default'>}
     */
    this.getInitialValues = function() {
      var initialValues = {};
      for (var key in rows) {
        var value = rows[key].$select.val();
        if ('' + value === '' + parseInt(value)) {
          initialValues[key] = value;
        }
        else if ('default' === value) {
          initialValues[key] = 'default';
        }
        else if ('disabled' === value) {
          initialValues[key] = 'disabled';
        }
        else {
          throw "Invalid value for key '" + key + "'.";
        }
      }
      return initialValues;
    };

    /**
     * @returns {Object.<jQuery>}
     */
    this.getRows = function() {
      return trRows;
    };

    /**
     * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
     * @param {Drupal.crumbs.TreeExpandVisibilityModel} treeExpandVisibilityModel
     * @param {Drupal.crumbs.Hierarchy} hierarchy
     *
     * @returns {Drupal.crumbs.TreeTable}
     */
    this.initTreeTableMechanics = function(treeExpandModel, treeExpandVisibilityModel, hierarchy) {
      /** @type {Drupal.crumbs.TreeTable} */
      var treeTable = new Drupal.crumbs.TreeTable($table, trRows, hierarchy, treeExpandModel);
      treeTable.initSubtreeExpandMechanics();
      treeExpandModel.observeExpandedKeys(treeTable);
      treeExpandVisibilityModel.observeExpandedKeys(treeTable);
      return treeTable;
    };

    /**
     * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
     * @param {Drupal.crumbs.MasterValueModel} masterValueModel
     * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
     * @returns {Drupal.crumbs.AdminDropdownTable}
     */
    this.createWidget = function(focusGroupModel, masterValueModel, effectiveValueModel) {
      return Drupal.crumbs.AdminDropdownTable.create(rows, $table, focusGroupModel, masterValueModel, effectiveValueModel);
    };

    /**
     * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
     * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
     */
    this.rowStatusControls = function(masterStatusModel, effectiveValueModel) {
      var controls = new Drupal.crumbs.RowStatusControls(rows, $table, masterStatusModel);
      masterStatusModel.observeMasterStatuses(controls);
      effectiveValueModel.observeEffectiveValues(controls);
      return controls;
    };

    this.rowsToggleButtons = function(masterStatusModel, effectiveValueModel) {
      var controls = new Drupal.crumbs.RowsToggleButtons(rows, $table, masterStatusModel);
      masterStatusModel.observeMasterStatuses(controls);
      effectiveValueModel.observeEffectiveValues(controls);
      return controls;
    };

    /**
     * Inserts a column where each cell says '..' if the row has any (grand)
     * children with explicit (= overridden) values.
     *
     * @param {Drupal.crumbs.Hierarchy} hierarchy
     */
    this.childrenColumn = function(hierarchy) {
      new Drupal.crumbs.HasChildrenIndicators(rows, $table, hierarchy);
    };

    /**
     * Replaces the displayed plugin keys. E.g. "taxonomy.termReference.*"
     * becomes "termReference", assuming that the "taxonomy" part is already
     * displayed in the parent plugin.
     */
    this.abbreviateRowLabels = function() {
      for (var key in rows) {
        var abbrevKey = Drupal.crumbs.abbreviateKey(key);
        rows[key].$tdTitle.text(abbrevKey);
      }
    };
  };

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @returns {Object.<bool>}
   */
  function rowsExtractKeys(rows) {
    var keys = {};
    for (var key in rows) {
      keys[key] = true;
    }
    return keys;
  }

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @returns {Object.<bool>}
   */
  function rowsExtractRootKeys(rows) {
    var rootKeys = {'*': true};
    for (var key in rows) {
      var row = rows[key];
      if (!row.hasDisabledOption) {
        rootKeys[key] = true;
      }
    }
    return rootKeys;
  }

  /**
   *
   * @param {jQuery} $table
   * @param {string} rootId
   * @returns {Object.<Drupal.crumbs.dropdowns.RowInfo>}
   *   Row objects by key.
   */
  function extractRows($table, rootId) {

    var rowsByKey = {};
    $('tr', $table).each(function(){
      var $tr = $(this);
      var $select = $('select', $tr).first();
      if (!$select.length) {
        return;
      }
      var row = new Drupal.crumbs.dropdowns.RowInfo($tr, $select, rootId);
      rowsByKey[row.key] = row;
    });
    return rowsByKey;
  }

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   */
  function rowsGetTrRows(rows) {
    var trRows = {};
    for (var key in rows) {
      trRows[key] = rows[key].$tr;
    }
    return trRows;
  }

})();
