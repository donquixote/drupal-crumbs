/*global jQuery, document, Drupal */

(function () {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};
  if (!Drupal.crumbs.dropdowns) Drupal.crumbs.dropdowns = {};

  /**
   * @param {jQuery} $tr
   * @param {jQuery} $select
   * @param {string} rootId
   * @constructor
   */
  Drupal.crumbs.dropdowns.RowInfo = function($tr, $select, rootId) {
    var key = selectExtractKey($select);
    var $tdSelect = $select.parent();
    while ('TD' !== $tdSelect[0].tagName) {
      $tdSelect = $tdSelect.parent();
    }
    this.$tdSelect = $tdSelect;
    var $defaultOption = $select.children('option[value="default"]');
    var $disabledOption = $select.children('option[value="disabled"]');
    this.hasDisabledOption = (1 === $disabledOption.length);
    if (this.hasDisabledOption) {
      var $inheritOption = $defaultOption;
    }
    else {
      $disabledOption = null;
      $inheritOption = null;
    }
    // this.id = $tr.attr('id');
    this.key = key;
    this.parentKey = Drupal.crumbs.keyGetParentKey(key);
    this.name = $select.attr('name');
    this.$tdTitle = $('> td:first-child', $tr);
    this.$inheritOption = $inheritOption;
    this.$disabledOption = $disabledOption;
    this.$defaultOption = $defaultOption;
    this.$tr = $tr;
    this.$select = $select;
    // this.parentId = extractParentId($tr, rootId);
    // this.inheritanceParentId = this.hasDisabledOption ? this.parentId : null;
    this.$tdWeight = $('> .crumbs-column-weight', $tr);
    this.$tdChildren = $('> .crumbs-column-children', $tr);
  };

  /**
   * Extract the plugin key from a select element.
   *
   * E.g. for <select name="crumbs_weights[rules.x.y.z][weight]" ..>
   * The plugin key would be "x.y.z".
   *
   * @returns {string|null}
   */
  function selectExtractKey($select) {
    var nameAttribute, m;
    if (nameAttribute = $select.attr('name')) {
      if (m = nameAttribute.match(/^crumbs_weights\[rules\.(.*)\]\[weight\]$/)) {
        return m[1];
      }
    }
    return null;
  }

})();
