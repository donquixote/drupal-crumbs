/*global jQuery, document, Drupal */

(function(){
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.FocusGroupObserverInterface = function() {};

  /**
   * Adds a key to the focus group.
   *
   * @param {string} key
   */
  Drupal.crumbs.FocusGroupObserverInterface.prototype.focusAddKey = function(key) {};

  /**
   * Removes a key from the focus group.
   *
   * @param {string} key
   */
  Drupal.crumbs.FocusGroupObserverInterface.prototype.focusRemoveKey = function(key) {};

  /**
   * Class to manage a group of keys to be focused/highlighted.
   * (we assume each key to identify a row in a table)
   *
   * @param {Drupal.crumbs.InheritanceModel} inheritanceModel
   *
   * @constructor
   * @implements {Drupal.crumbs.InheritanceObserverInterface}
   */
  Drupal.crumbs.FocusGroupModel = function(inheritanceModel) {

    var self = this;

    /**
     * The actively focused key.
     *
     * @type {string}
     */
    var focusedKey;

    /**
     * The parent key of the focused group.
     *
     * @type {string}
     */
    var focusedGroupRootKey;

    /**
     * All focused keys.
     * Format: focusedKeys['x.y.*'] = true;
     *
     * @type {{}}
     */
    var focusedKeys = {};

    /**
     * @type {Drupal.crumbs.FocusGroupObserverInterface[]}
     */
    var observers = [];

    /**
     * @param {string} key
     */
    function focusAddKey(key) {
      focusedKeys[key] = true;
      for (var i = 0; i < observers.length; ++i) {
        observers[i].focusAddKey(key);
      }
    }

    /**
     * @param {string} key
     */
    function focusRemoveKey(key) {
      delete focusedKeys[key];
      for (var i = 0; i < observers.length; ++i) {
        observers[i].focusRemoveKey(key);
      }
    }

    /**
     * @param {string} key
     */
    function focusAddKeysRecursive(key) {
      focusAddKey(key);
      for (var childKey in inheritanceModel.getEffectiveChildKeys(key)) {
        focusAddKeysRecursive(childKey);
      }
    }

    /**
     * @param {string} key
     * @returns {boolean}
     */
    this.isKeyInFocusGroup = function(key) {
      return focusedKeys[key] ? true : false;
    };

    /**
     * Sets the focus to the group that the clicked row belongs to.
     * (and removes focus from other rows/groups)
     *
     * @param {string} key
     *   Any key that is contained in the group to focus.
     */
    this.focusGroupWithKey = function(key) {
      focusedKey = key;
      var effectiveParentKey = inheritanceModel.getEffectiveParentKey(key);
      if (focusedGroupRootKey === effectiveParentKey) {
        return;
      }
      focusedGroupRootKey = effectiveParentKey;
      for (var oldKey in focusedKeys) {
        focusRemoveKey(oldKey);
      }
      focusAddKeysRecursive(effectiveParentKey);
    };

    /**
     * @param {Object.<string>} updatedEffectiveParentKeys
     */
    this.updateEffectiveParentKeys = function (updatedEffectiveParentKeys) {
      self.focusGroupWithKey(focusedKey);
    };

    /**
     * @param {Drupal.crumbs.FocusGroupObserverInterface} observer
     */
    this.observeFocusGroup = function(observer) {
      observers.push(observer);
      for (var key in focusedKeys) {
        observer.focusAddKey(key);
      }
    }
  };

  /**
   * Factory function.
   * Creates a FocusGroupModel instance, and subscribes to effective parent key
   * updates fired by the inheritance model.
   *
   * @param {Drupal.crumbs.InheritanceModel} inheritanceModel
   * @returns {Drupal.crumbs.FocusGroupModel}
   */
  Drupal.crumbs.FocusGroupModel.createInstance = function(inheritanceModel) {
    var model = new Drupal.crumbs.FocusGroupModel(inheritanceModel);
    inheritanceModel.observeEffectiveParentKeys(model);
    return model;
  }
})();

