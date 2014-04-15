/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.MasterWeightObserverInterface = function() {};

  /**
   * @param {string} key
   *   E.g. 'x.y.*'
   * @param {'enabled'|'disabled'|'default'} status
   *   The updated master value for the given key.
   */
  Drupal.crumbs.MasterWeightObserverInterface.prototype.updateMasterWeight = function(key, status) {};

  /**
   * Class to manage a number of keys with numeric weight
   * 'default'.
   * (we assume each key to identify a row in a table)
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   */
  Drupal.crumbs.MasterWeightModel = function() {

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function(masterValues) {
    };
  };
})();
