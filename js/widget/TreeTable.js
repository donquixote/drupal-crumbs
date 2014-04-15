/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {jQuery} $table
   * @param {Object.<jQuery>} rows
   * @param {Drupal.crumbs.TreeExpandModel} treeExpandModel
   *
   * @constructor
   * @implements {Drupal.crumbs.TreeExpandObserverInterface}
   * @implements {Drupal.crumbs.TreeExpandVisibilityObserverInterface}
   */
  Drupal.crumbs.TreeTable = function($table, rows, treeExpandModel) {

    /**
     * @type {Drupal.crumbs.RowClassSwitcher}
     */
    var rowClassSwitcher = new Drupal.crumbs.RowClassSwitcher(rows);

    /**
     * @param {string} key
     * @param {jQuery} $tr
     * @param {jQuery} $td
     *   The prepended rowspan cell.
     */
    function initRow(key, $tr, $td) {
      if (!treeExpandModel.keyIsExpansible(key)) {
        return;
      }
      if (!$td || !$td.length) {
        return;
      }
      // Add a clickable expand icon.
      var $expander = jQuery('<span>').addClass('crumbs-TreeTable-expander').prependTo($td);
      // var $arrow = $('<span>').prependTo($expander);
      $expander.click(function(){
        treeExpandModel.keyToggleExpanded(key);
      });
    }

    /**
     * @param {Drupal.crumbs.Hierarchy} hierarchy
     */
    this.initMechanics = function(hierarchy) {
      $table.children('tbody, thead, tfoot').children('tr').each(function(){
        jQuery(this).children('td').first().addClass('td-title');
      });
      var rowspanCells = prependRowspanCells($table, rows, hierarchy);
      for (var key in rows) {
        initRow(key, rows[key], rowspanCells[key]);
      }
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
  };

  /**
   * Prepend table cells with rowspan before each row.
   *
   * @param {jQuery} $table
   * @param {Object.<jQuery>} rows
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   */
  function prependRowspanCells($table, rows, hierarchy) {
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
      rows[parentKey].children('td').first().attr('colspan', maxDepth - depth + 1);
      var $td = jQuery('<td>').attr('rowspan', rowspan).addClass('crumbs-indent').prependTo(rows[parentKey]);
      var $div = jQuery('<div>').appendTo($td);
      rowspanCells[parentKey] = $div;
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
