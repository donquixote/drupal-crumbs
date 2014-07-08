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
  Drupal.crumbs.RowsToggleButtons = function(rows, $table, masterStatusModel) {

    /**
     * @type {Object.<RowToggleButtons>}
     */
    var rowControls = {};

    for (var key in rows) {
      rowControls[key] = new RowToggleButtons(key, rows[key], masterStatusModel);
    }

    // Modify table header cells colspan
    cellIncrementAttribute($('> thead > tr > th:nth-child(2)', $table), 'colspan', 2);

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
  function RowToggleButtons(key, row, masterStatusModel) {
    row.$tdSelect.hide();

    var rowIsDisabledByDefault = !row.hasDisabledOption || key === '*';
    var disabledState = rowIsDisabledByDefault ? 'default' : 'disabled';

    var initialStatus = masterStatusModel.getMasterStatus(key);

    var $tdWeight = $('<td>').insertAfter(row.$tdSelect);
    var $tdEnable = $('<td>').insertAfter(row.$tdSelect);
    var $tdInherit = $('<td>').insertAfter(row.$tdSelect);

    var onOffButton = new RowOnOffButton(disabledState);
    onOffButton.getToggleButton().appendTo($tdEnable);
    onOffButton.onClickUpdateStatus(masterStatusModel, key);
    onOffButton.setStatus(initialStatus);

    var inheritButton;
    if (!rowIsDisabledByDefault) {
      inheritButton = new RowInheritButton();
      inheritButton.getInheritButton().appendTo($tdInherit);
      inheritButton.onClickUpdateStatus(masterStatusModel, key);
      inheritButton.setStatus(initialStatus);
    }

    var $weight = $('<div>')
      .addClass('crumbs-div-weight')
      .appendTo($tdWeight);

    /**
     * @param {string} status
     */
    this.updateStatus = function(status) {
      onOffButton.setStatus(status);
      if (inheritButton) {
        inheritButton.setStatus(status);
      }
    };

    /**
     * @param {'default'|'disabled'|int} value
     */
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
   * @constructor
   */
  function RowOnOffButton(disabledState) {

    /** @type {jQuery} */
    var $toggleButton = $('<div>')
      .addClass('toggle-enable')
      .prepend('<div>&nbsp;</div>');

    /**
     * @param {'enabled'|'disabled'|'default'} status
     */
    this.setStatus = function (status) {
      switch (status) {
        case 'enabled':
          $toggleButton.addClass('enabled');
          $toggleButton.removeClass('off');
          break;
        default:
          $toggleButton.removeClass('enabled');
          $toggleButton.addClass('off');
      }
    };

    /**
     * @returns {jQuery}
     */
    this.getToggleButton = function () {
      return $toggleButton;
    };

    /**
     * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
     * @param {string} key
     */
    this.onClickUpdateStatus = function (masterStatusModel, key) {
      $toggleButton.click(function () {
        var currentStatus = masterStatusModel.getMasterStatus(key);
        if ('enabled' === currentStatus) {
          masterStatusModel.setMasterStatus(key, disabledState);
        }
        else if (disabledState === currentStatus) {
          masterStatusModel.setMasterStatus(key, 'enabled');
        }
      });
    };
  }

  /**
   * @param {Drupal.crumbs.EffectiveValueModel} effectiveValueModel
   *
   * @constructor
   */
  function RowInheritButton(effectiveValueModel) {

    /** @type {jQuery} */
    var $inheritButton = $('<span>')
      .html('Inherit')
      .addClass('button button-inherit');

    /**
     * @returns {jQuery}
     */
    this.getInheritButton = function() {
      return $inheritButton;
    };

    /**
     * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
     * @param {string} key
     */
    this.onClickUpdateStatus = function (masterStatusModel, key) {
      $inheritButton.click(function () {
        if ('default' === masterStatusModel.getMasterStatus(key)) {
          masterStatusModel.setMasterStatus(key, 'default');
        }
        else {
          masterStatusModel.setMasterStatus(key, 'default');
        }
      });
    };

    /**
     * @param {'enabled'|'disabled'|'default'} status
     */
    this.setStatus = function(status) {
      if ('default' === status) {
        $inheritButton.addClass('active');
      }
      else {
        $inheritButton.removeClass('active');
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
