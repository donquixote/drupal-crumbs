/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {Object.<bool>} keys
   *   E.g. keys['x.y.*'] = true;
   * @param {Object.<bool>} rootKeys
   *   E.g. rootKeys['x.y.*'] = true;
   * @constructor
   */
  Drupal.crumbs.HierarchyExtractor = function(keys, rootKeys) {

    /**
     * @type {Object.<string>}
     *   E.g. parentKeys['x.y.*'] = 'x.*'
     */
    var parentKeys = {};

    /**
     * @type {Object.<string>}
     *   E.g. parentKeys['x.y.*'] = 'x.*'
     */
    var inheritanceParentKeys = {};

    for (var key in keys) {
      parentKeys[key] = Drupal.crumbs.keyGetParentKey(key);
      inheritanceParentKeys[key] = parentKeys[key];
    }

    for (var rootKey in rootKeys) {
      inheritanceParentKeys[rootKey] = null;
    }

    /**
     * @returns {Drupal.crumbs.Hierarchy}
     */
    this.buildHierarchy = function() {
      return new Drupal.crumbs.Hierarchy(parentKeys);
    };

    /**
     * @returns {Drupal.crumbs.Hierarchy}
     */
    this.buildInheritanceHierarchy = function() {
      return new Drupal.crumbs.Hierarchy(inheritanceParentKeys);
    };
  };

  /**
   * @param {string} key
   * @returns {string|null}
   */
  Drupal.crumbs.keyGetParentKey = function(key) {
    if ('*' === key) {
      return null;
    }
    var pieces = key.split('.');
    var piecesStripped = (pieces[pieces.length - 1] === '*')
      ? pieces.slice(0, -1)
      : pieces;
    return (piecesStripped.length === 1)
      ? '*'
      : piecesStripped.slice(0, -1).join('.') + '.*';
  };

  /**
   * @param {string} key
   * @returns {string}
   */
  Drupal.crumbs.abbreviateKey = function(key) {
    if ('*' === key) {
      return '*';
    }
    var pieces = key.split('.');
    var endpiece = pieces.pop();
    if ('*' === endpiece) {
      endpiece = pieces.pop() + '.*';
    }
    var dots = '';
    for (var i = 0; i < pieces.length; ++i) {
      dots += '.';
    }
    return dots + endpiece;
  };
})();
