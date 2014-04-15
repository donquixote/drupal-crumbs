/*global jQuery, document, Drupal */

(function() {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * Class that represents a hierarchy of keys.
   *
   * @param {Object.<string>} parentKeys
   *   Maps plugin keys to their parent keys.
   *   Example: parentKeys['x.y.z.*'] = 'x.y.*';
   *
   * @constructor
   */
  Drupal.crumbs.Hierarchy = function(parentKeys) {

    /**
     * Maps parent keys to their children.
     * Example: hierarchy['x.y.*']['x.y.z.*'] = true;
     *
     * @type {Object.<Object.<bool>>}
     */
    var hierarchy = {};

    /**
     * Keys that have no further parent.
     * Example: rootKeys['*'] = true
     *
     * @type {Object.<bool>}
     */
    var rootKeys = {};

    /**
     * Keys that have at least one child key.
     *
     * @type {Object.<true>}
     */
    var keysWithChildren = {};

    for (var key in parentKeys) {
      var parentKey = parentKeys[key];
      if ('string' === typeof parentKey) {
        keysWithChildren[parentKey] = true;
        if (undefined === hierarchy[parentKey]) {
          hierarchy[parentKey] = {};
        }
        hierarchy[parentKey][key] = true;
      }
      else {
        rootKeys[key] = true;
      }
    }

    /**
     * The depth of each key.
     *
     * @type {Object.<int>}
     */
    var keyDepths = calcKeyDepths(hierarchy, rootKeys);

    /**
     * The maximum number of steps
     *
     * @type {number}
     */
    var maxDepth = calcMaxKeyDepth(keyDepths);

    /**
     * Checks whether the key has any children.
     *
     * @param {string} parentKey
     *   E.g. 'x.y.*'
     * @returns {bool}
     */
    this.hasChildren = function(parentKey) {
      return (undefined !== hierarchy[parentKey]);
    };

    /**
     * Gets the children of a given key.
     *
     * @param {string} parentKey
     *   E.g. 'x.y.*'
     * @returns {Object.<bool>}
     *   E.g. {'x.y.z0.*': true, 'x.y.z1.*': true}
     */
    this.getChildKeys = function(parentKey) {
      return (undefined !== hierarchy[parentKey])
        ? hierarchy[parentKey]
        : {};
    };

    /**
     * Checks whether a key has a parent.
     *
     * @param {string} key
     * @returns {bool}
     *   true, if the key has a parent key.
     */
    this.hasParentKey = function(key) {
      return undefined !== parentKeys[key]
        && null !== parentKeys[key];
    };

    /**
     * Gets the parent key.
     *
     * @param {string} key
     * @returns {string|null}
     *   The parent key
     */
    this.getParentKey = function(key) {
      return (undefined !== parentKeys[key])
        ? parentKeys[key]
        : null;
    };

    /**
     * Gets the root keys.
     *
     * @returns {Object.<bool>}
     *   E.g. {'*': true}
     */
    this.getRootKeys = function() {
      return rootKeys;
    };

    /**
     * @param {string} key
     * @returns {number}
     */
    this.keyGetDepth = function(key) {
      if (!keyDepths.hasOwnProperty(key)) {
        throw 'Unkown key ' + key;
      }
      return keyDepths[key];
    };

    /**
     * @returns {number}
     */
    this.getMaxDepth = function() {
      return maxDepth;
    };

    /**
     * @returns {Object.<true>}
     */
    this.getKeysWithChildren = function() {
      return keysWithChildren;
    }
  };

  /**
   * @param {Object.<Object.<bool>>} hierarchy
   * @param {Object.<bool>} rootKeys
   */
  function calcKeyDepths(hierarchy, rootKeys) {
    var depths = {};
    function setKeyDepthsRecursive(key, depth) {
      depths[key] = depth;
      for (var childKey in hierarchy[key]) {
        setKeyDepthsRecursive(childKey, depth + 1);
      }
    }
    for (var rootKey in rootKeys) {
      setKeyDepthsRecursive(rootKey, 0);
    }
    return depths;
  }

  /**
   * @param {Object.<int>} keyDepths
   * @returns {number}
   */
  function calcMaxKeyDepth(keyDepths) {
    var max = 0;
    for (var key in keyDepths) {
      if (keyDepths[key] > max) {
        max = keyDepths[key];
      }
    }
    return max;
  }

})();
