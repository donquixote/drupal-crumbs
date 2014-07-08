/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {jQuery} $table
   * @param {Object.<jQuery>} rows
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
   *
   * @constructor
   * @implements {Drupal.crumbs.TreeExpandObserverInterface}
   * @implements {Drupal.crumbs.TreeExpandVisibilityObserverInterface}
   */
  Drupal.crumbs.TreeTable = function ($table, rows, hierarchy, treeExpandModel) {

    /**
     * @type {Object.<jQuery>|null}
     */
    var onOffCells = null;

    /**
     * @type {Object.<jQuery>|null}
     */
    var inheritOverrideCells;

    /**
     * @type {Object.<jQuery>|null}
     */
    var rowspanCells;

    /**
     * @type {Drupal.crumbs.RowClassSwitcher}
     */
    var rowClassSwitcher = new Drupal.crumbs.RowClassSwitcher(rows);

    /**
     */
    this.initSubtreeExpandMechanics = function() {
      prepareOnce();
      for (var key in rowspanCells) {
        var $div = jQuery('<div>').appendTo(rowspanCells[key]);
        initRowExpandControls(key, $div, treeExpandModel);
      }
    };

    /**
     * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
     * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
     * @param {Drupal.crumbs.ExplicityModel} explicityModel
     */
    this.initFuzzyCheckboxes = function(masterStatusModel, effectiveValueModel, explicityModel) {
      prepareOnce();
      var fuzzyCheckboxes = new Drupal.crumbs.FuzzyCheckboxes(onOffCells, inheritOverrideCells);
      fuzzyCheckboxes.onClickSetStatus(masterStatusModel);
      explicityModel.observeExplicity(fuzzyCheckboxes);
      effectiveValueModel.observeEffectiveValues(fuzzyCheckboxes);
    };

    /**
     * @param {string} key
     * @param {bool} isExpanded
     */
    this.updateKeyIsExpanded = function (key, isExpanded) {
      rowClassSwitcher.rowConditionalClass(key, !isExpanded, 'crumbs-TreeTable-collapsed');
    };

    /**
     * @param {Object.<bool>} updated
     */
    this.updateVisibleKeys = function (updated) {
      rowClassSwitcher.rowsConditionalClass(updated, 'crumbs-TreeTable-hidden', true);
    };

    function prepareOnce() {
      if (null !== onOffCells) {
        // Already prepared.
        return;
      }
      $table.children('tbody, thead, tfoot').children('tr').each(function(){
        jQuery(this).children('td').first().addClass('td-title');
      });
      var colspanColumn = {};
      for (var key in rows) {
        /** @type {Object.<jQuery>} */
        colspanColumn[key] = rows[key].children('td').first();
      }
      onOffCells = prependCells($table, rows, 'crumbs-pivot');
      inheritOverrideCells = prependRowspanCells($table, rows, hierarchy, 'crumbs-pivot', colspanColumn);
      rowspanCells = prependRowspanCells($table, rows, hierarchy, 'crumbs-pivot', colspanColumn);
    }
  };

  /**
   * @param {string} key
   * @param {jQuery} $rowExpandControl
   *   The prepended rowspan cell.
   * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
   */
  function initRowExpandControls(key, $rowExpandControl, treeExpandModel) {
    if (!treeExpandModel.keyIsExpansible(key)) {
      return;
    }
    if (!$rowExpandControl || !$rowExpandControl.length) {
      return;
    }
    // Add a clickable expand icon.
    var $expander = jQuery('<span>')
      .addClass('crumbs-TreeTable-expander')
      .prependTo($rowExpandControl);
    // var $arrow = $('<span>').prependTo($expander);
    $expander.click(function(){
      treeExpandModel.keyToggleExpanded(key);
    });
  }

  /**
   * Prepend table cells before each row.
   *
   * @param {jQuery} $table
   * @param {Object.<jQuery>} rows
   * @param {string} className
   *
   * @returns {Object.<jQuery>}
   */
  function prependCells($table, rows, className) {
    cellIncrementAttribute(jQuery('> thead > tr > th:first-child', $table), 'colspan', 1);
    var newCells = {};
    for (var key in rows) {
      newCells[key] = jQuery('<td>')
        .addClass(className)
        .prependTo(rows[key]);
    }
    return newCells;
  }

  /**
   * Prepend table cells with rowspan before each row.
   *
   * @param {jQuery} $table
   * @param {Object.<jQuery>} rows
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   * @param {string} className
   * @param {Object.<jQuery>} colspanColumn
   *
   * @returns {Object.<jQuery>}
   */
  function prependRowspanCells($table, rows, hierarchy, className, colspanColumn) {
    var maxDepth = hierarchy.getMaxDepth();
    cellIncrementAttribute(jQuery('> thead > tr > th:first-child', $table), 'colspan', maxDepth + 1);

    /** @type {Object.<jQuery>} */
    var rowspanCells = {};

    /**
     * @param {string} parentKey
     * @param {int} depth
     */
    function prependRowspanCellsRecursive(parentKey, depth) {
      var childKeys = hierarchy.getChildKeys(parentKey);
      var rowspan = 1;
      for (var key in childKeys) {
        rowspan += prependRowspanCellsRecursive(key, depth + 1);
      }

      cellIncrementAttribute(colspanColumn[parentKey], 'colspan', maxDepth - depth);

      rowspanCells[parentKey] = jQuery('<td>')
        .attr('rowspan', rowspan)
        .addClass(className)
        .prependTo(rows[parentKey]);

      return rowspan;
    }

    prependRowspanCellsRecursive('*', 0);
    return rowspanCells;
  }

  /**
   * @param {jQuery} $cell
   * @param {string} attributeName
   * @param {int} increment
   */
  function cellIncrementAttribute($cell, attributeName, increment) {
    var attributeValue = $cell.attr(attributeName);
    if ('' + attributeValue === '' + parseInt(attributeValue)) {
      attributeValue = parseInt(attributeValue);
    }
    else {
      attributeValue = 1;
    }
    $cell.attr(attributeName, attributeValue + increment);
  }

})();
