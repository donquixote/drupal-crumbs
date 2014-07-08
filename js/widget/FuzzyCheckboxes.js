/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<jQuery>} onOffCells
   * @param {Object.<jQuery>} inheritOverrideCells
   *
   * @constructor
   * @implements {Drupal.crumbs.ExplicityObserverInterface}
   * @implements {Drupal.crumbs.EffectiveValueObserverInterface}
   */
  Drupal.crumbs.FuzzyCheckboxes = function(onOffCells, inheritOverrideCells) {

    /**
     * @type {Object.<jQuery>}
     */
    var checkboxes = buildCheckboxes(onOffCells);

    /**
     * @type {Object.<jQuery>}
     */
    var overrideControls = buildOverrideControls(inheritOverrideCells);

    /**
     * @param {string} key
     * @param {int|'disabled'} effectiveValue
     */
    this.updateEffectiveValue = function (key, effectiveValue) {
      var isEnabled = ('' + effectiveValue === '' + parseInt(effectiveValue));
      // This has to work with jQuery 1.4, where $.fn.prop() is not available.
      if (isEnabled) {
        checkboxes[key].attr('checked', 'checked');
      }
      else {
        checkboxes[key].removeAttr('checked');
      }
    };

    /**
     * @param {Object.<bool>} updatedExplicitKeys
     */
    this.updateExplicitKeys = function (updatedExplicitKeys) {
      for (var key in updatedExplicitKeys) {
        var isExplicit = updatedExplicitKeys[key];
        checkboxes[key].attr('disabled', !isExplicit);
        // overrideControls[key].html(isExplicit ? '!!!' : '==');
      }
    };

    /**
     * @param {Drupal.crumbs.MasterStatusModel} masterStatusModel
     */
    this.onClickSetStatus = function(masterStatusModel) {
      jQuery.each(checkboxes, function(key, $checkbox) {
        $checkbox.change(function() {
          masterStatusModel.toggleEnabled(key);
          // return false;
        });
      });
      jQuery.each(overrideControls, function(key, $div) {
        $div.click(function(){
          masterStatusModel.toggleExplicity(key);
        });
      });
    };
  };

  /**
   * @param {Object.<jQuery>} cells
   */
  function buildCheckboxes(cells) {
    /** @type {Object.<jQuery>} */
    var checkboxes = {};
    for (var key in cells) {
      checkboxes[key] = jQuery('<input>')
        .attr('type', 'checkbox')
        .appendTo(cells[key]);
    }
    return checkboxes;
  }

  /**
   * @param {Object.<jQuery>} cells
   */
  function buildOverrideControls(cells) {
    /** @type {Object.<jQuery>} */
    var overrideControls = {};
    for (var key in cells) {
      overrideControls[key] = jQuery('<div>')
        .html('&nbsp;')
        .appendTo(cells[key])
        .addClass('crumbs-override-ctrl');
    }
    return overrideControls;
  }
})();
