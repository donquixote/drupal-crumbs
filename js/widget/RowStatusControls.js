/*global jQuery, document, Drupal */

(function () {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<Drupal.crumbs.dropdowns.RowInfo>} rows
   * @param {jQuery} $table
   * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterStatusObserverInterface}
   * @implements {Drupal.crumbs.EffectiveValueObserverInterface}
   */
  Drupal.crumbs.RowStatusControls = function(rows, $table, masterStatusModel) {

    /**
     * @type {Object.<RowControls>}
     */
    var rowControls = {};

    for (var key in rows) {
      rowControls[key] = new RowControls(key, rows[key], masterStatusModel);
    }

    // Modify table header cells colspan
    cellIncrementAttribute($('> thead > tr > th:nth-child(2)', $table), 'colspan', 3);

    /**
     * @param {string} key
     *   E.g. 'x.y.*'
     * @param {'enabled'|'disabled'|'default'} status
     *   The updated master value for the given key.
     */
    this.updateMasterStatus = function(key, status) {
      rowControls[key].updateStatus(status);
    };

    /**
     * @param {string} key
     * @param {int|'disabled'} effectiveValue
     */
    this.updateEffectiveValue = function(key, effectiveValue) {
      rowControls[key].updateEffectiveValue(effectiveValue);
    }
  };

  /**
   * @param {string} key
   * @param {Drupal.crumbs.dropdowns.RowInfo} row
   * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
   *
   * @constructor
   */
  function RowControls(key, row, masterStatusModel) {
    row.$tdSelect.hide();

    var $tdWeight = $('<td>').insertAfter(row.$tdSelect);
    var $tdEnable = $('<td>').insertAfter(row.$tdSelect);
    var $tdDisable = $('<td>').insertAfter(row.$tdSelect);
    var $tdInherit = $('<td>').insertAfter(row.$tdSelect);

    var $weight = $('<div>')
      .addClass('crumbs-div-weight')
      .appendTo($tdWeight);

    /** @type {Object.<jQuery>} */
    var buttons = {};

    if (row.hasDisabledOption && key !== '*') {
      buttons['default'] = $('<span>')
        .html('Inherit')
        .addClass('button button-inherit')
        .appendTo($tdInherit)
        .click(function () {
          masterStatusModel.setMasterStatus(key, 'default');
        });

      buttons['disabled'] = $('<span>')
        .html('OFF')
        .addClass('button button-disable')
        .appendTo($tdDisable)
        .click(function () {
          masterStatusModel.setMasterStatus(key, 'disabled');
        });
    }
    else {
      buttons['default'] = $('<span>')
        .html('OFF')
        .addClass('button button-disable')
        .appendTo($tdDisable)
        .click(function () {
          masterStatusModel.setMasterStatus(key, 'default');
        });
    }

    buttons['enabled'] = $('<span>')
      .html('ON')
      .addClass('button button-disable')
      .appendTo($tdEnable)
      .click(function(){
        masterStatusModel.setMasterStatus(key, 'enabled');
      });

    this.updateStatus = function(status) {
      for (var key in buttons) {
        if (key !== status) {
          buttons[key].removeClass('active');
        }
        else {
          buttons[key].addClass('active');
        }
      }
    };

    this.updateEffectiveValue = function(value) {
      if ('' + value === '' + parseInt(value)) {
        $weight.html(value);
        $weight.show();
      }
      else {
        $weight.hide();
      }
    };
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
