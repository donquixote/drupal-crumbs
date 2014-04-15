/*global jQuery, document, Drupal */

(function(){
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.EffectiveValueObserverInterface = function() {};

  /**
   * @param {string} key
   * @param {int|'disabled'} effectiveValue
   */
  Drupal.crumbs.EffectiveValueObserverInterface.prototype.updateEffectiveValue = function(key, effectiveValue) {};

  /**
   * Class to calculate inherited values.
   *
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @param {Drupal.crumbs.InheritanceModel} inheritanceModel
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   */
  Drupal.crumbs.EffectiveValueModel = function(masterValueModel, inheritanceModel) {

    /**
     * @type {Object.<int|'disabled'>}
     */
    var effectiveValues = {};

    /**
     * @type {Drupal.crumbs.EffectiveValueObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {string} key
     * @param {int|'disabled'} value
     */
    function updateValueRecursive(key, value) {
      if (value === effectiveValues[key]) {
        return;
      }
      effectiveValues[key] = value;
      for (var i = 0; i < observers.length; ++i) {
        observers[i].updateEffectiveValue(key, value);
      }
      for (var childKey in inheritanceModel.getEffectiveChildKeys(key)) {
        updateValueRecursive(childKey, value);
      }
    }

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function (masterValues) {
      /** @type {Object.<int|'disabled'>} */
      var updated = {};
      var hasUpdate;
      for (var key in masterValues) {
        var effectiveParentKey = inheritanceModel.getEffectiveParentKey(key);
        var effectiveValue = masterValues[effectiveParentKey];
        if ('default' === effectiveValue) {
          effectiveValue = 'disabled';
        }
        if (effectiveValue !== effectiveValues[key]) {
          updated[key] = effectiveValues[key] = effectiveValue;
          hasUpdate = true;
        }
      }
      if (hasUpdate) {
        for (var updatedKey in updated) {
          for (var i = 0; i < observers.length; ++i) {
            observers[i].updateEffectiveValue(updatedKey, updated[updatedKey]);
          }
        }
      }
    };

    /**
     * @param {Drupal.crumbs.EffectiveValueObserverInterface} observer
     */
    this.observeEffectiveValues = function(observer) {
      observers.push(observer);
      for (var key in effectiveValues) {
        observer.updateEffectiveValue(key, effectiveValues[key]);
      }
    };

    /**
     * @param {string} key
     * @returns {int|'disabled'|null}
     */
    this.keyGetEffectiveValue = function(key) {
      return effectiveValues[key];
    };
  };

  /**
   * Factory function.
   * Creates an EffectiveValueModel object, and subscribes it to updates from
   * masterValueModel and inheritanceModel.
   *
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @param {Drupal.crumbs.InheritanceModel} inheritanceModel
   *
   * @returns {Drupal.crumbs.EffectiveValueModel}
   */
  Drupal.crumbs.EffectiveValueModel.createInstance = function(masterValueModel, inheritanceModel) {
    var model = new Drupal.crumbs.EffectiveValueModel(masterValueModel, inheritanceModel);
    masterValueModel.observeMasterValues(model);
    return model;
  };
})();

