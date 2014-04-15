/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<jQuery>} rows
   *
   * @constructor
   */
  Drupal.crumbs.RowClassSwitcher = function(rows) {

    /**
     * @param {string} key
     * @param {bool} condition
     * @param {string} rowClass
     */
    function rowConditionalClass(key, condition, rowClass) {
      if (!rows[key]) {
        throw 'Unknown row key ' + key;
      }
      if (condition) {
        rows[key].addClass(rowClass);
      }
      else {
        rows[key].removeClass(rowClass);
      }
    }

    /**
     * @param {string} key
     * @param {bool} condition
     * @param {string} rowClass
     */
    this.rowConditionalClass = rowConditionalClass;

    /**
     * @param {Object.<bool>} keysWithCondition
     * @param {string} rowClass
     * @param {bool} [negate]
     *   If true, each condition will be negated.
     */
    this.rowsConditionalClass = function (keysWithCondition, rowClass, negate) {
      for (var key in keysWithCondition) {
        rowConditionalClass(key, !negate === keysWithCondition[key], rowClass);
      }
    };

    /**
     * @param {function} callback
     */
    this.rowsDynamicClass = function (callback) {
      for (var key in rows) {
        var rowClass = callback(key);
        if (rowClass) {
          rows[key].addClass(rowClass);
        }
      }
    };
  };

})();
