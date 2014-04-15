/*global jQuery, document, Drupal */

(function () {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @param {jQuery} $table
   * @param {Drupal.crumbs.Hierarchy} hierarchy
   *
   * @constructor
   */
  Drupal.crumbs.HasChildrenIndicators = function(rows, $table, hierarchy) {

    for (var key in rows) {
      var $td = $('<td>').addClass('crumbs-column-children').insertBefore(rows[key].$tdSelect);
      if (hierarchy.hasChildren(key)) {
        var $div = $('<div>').html('..').appendTo($td);
      }
    }

    // Modify table header cells colspan
    cellIncrementAttribute($('> thead > tr > th:nth-child(1)', $table), 'colspan', 1);
  };

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
