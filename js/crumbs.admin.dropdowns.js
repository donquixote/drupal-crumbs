/*global jQuery, document, Drupal */


(function($, lib) {
  "use strict";

  Drupal.behaviors.crumbsAdminDropdowns = {
    attach: function (context, settings) {
      $('table#crumbs_weights_dropdowns', context).once('crumbs-weights-dropdowns', function () {
        var $table = $(this);
        $table.treeTable();
        var rootId = 'crumbs-rule-3389dae361af79b04c9c8e7057f60cc6';
        var rows = extractRows($table, rootId);
        rowsColorizeDisabledOptions(rows);
        var values = rowsExtractValues(rows);
        var selectElements = rowsExtractX(rows, '$select');
        var parentKeys = rowsExtractX(rows, 'parentKey');
        var hierarchy = new Hierarchy(parentKeys);
        var inheritanceModel = new InheritanceModel(values, hierarchy);
        var trElements = rowsExtractX(rows, '$tr');
        var inheritOptionElements = rowsExtractX(rows, '$inheritOption');
        inheritanceModel.onEffectiveValueUpdate(new SelectOptionUpdater(inheritOptionElements, hierarchy));
        inheritanceModel.onEffectiveValueUpdate(new TransparencyUpdater(trElements, hierarchy));
        inheritanceModel.onEffectiveValueUpdate(new BackgroundUpdater(trElements));
        for (var key in selectElements) {
          (function(key) {
            var $select = selectElements[key];
            $select.change(function() {
              var value = $select.val();
              inheritanceModel.setValue(key, value);
            });
          })(key);
        }
      });
    }
  };

  $.extend(Drupal, {crumbs: {}});

  /**
   * @interface
   */
  Drupal.crumbs.RowUpdaterInterface = function() {};

  /**
   * @param {string} key
   * @param value
   * @param effectiveValue
   */
  Drupal.crumbs.RowUpdaterInterface.prototype.updateRow = function(key, value, effectiveValue) {};

  /**
   * @param {{jQuery}} trElements
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function BackgroundUpdater(trElements) {
    this.updateRow = function (key, value, effectiveValue) {
      var $tr = trElements[key];
      if ('disabled' === effectiveValue) {
        $tr.addClass('crumbs-rule-disabled');
        $tr.removeClass('crumbs-rule-enabled');
      }
      else {
        $tr.addClass('crumbs-rule-enabled');
        $tr.removeClass('crumbs-rule-disabled');
      }
    };
  }

  /**
   *
   * @param {{jQuery}} trElements
   * @param {Hierarchy} hierarchy
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function TransparencyUpdater(trElements, hierarchy) {
    this.updateRow = function (key, value, effectiveValue) {
      var $tr = trElements[key];
      if (hierarchy.hasParentKey(key) && 'default' === value) {
        $tr.addClass('crumbs-rule-inherit');
        $tr.removeClass('crumbs-rule-explicit');
      }
      else {
        $tr.addClass('crumbs-rule-explicit');
        $tr.removeClass('crumbs-rule-inherit');
      }
    };
  }

  /**
   *
   * @param {{jQuery}} inheritOptionElements
   * @param {Hierarchy} hierarchy
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function SelectOptionUpdater(inheritOptionElements, hierarchy) {
    this.updateRow = function(parentKey, value, effectiveValue) {
      for (var key in hierarchy.getChildKeys(parentKey)) {
        var $inheritOptionElement = inheritOptionElements[key];
        if ($inheritOptionElement) {
          $inheritOptionElement.text('Inherit (' + effectiveValue + ')');
          if ('disabled' === effectiveValue) {
            $inheritOptionElement.addClass('crumbs-option-disabled');
          }
          else {
            $inheritOptionElement.removeClass('crumbs-option-disabled');
          }
        }
      }
    }
  }

  /**
   *
   * @param {jQuery} $table
   * @param {string} rootId
   * @returns {{RowInfo}}
   */
  function extractRows($table, rootId) {

    var rowsById = {};
    $('tr', $table).each(function(){
      var $tr = $(this);
      var $select = $('select', $tr).first();
      if (!$select.length) {
        return;
      }
      var row = new RowInfo($tr, $select, rootId);
      rowsById[row.id] = row;
    });

    var rowsByKey = {};
    for (var id in rowsById) {
      var row = rowsById[id];
      row.parentKey = (null !== row.parentId)
        ? rowsById[row.parentId].key
        : null;
      rowsByKey[row.key] = row;
    }
    return rowsByKey;
  }

  /**
   * @param {jQuery} $tr
   * @param {jQuery} $select
   * @param {string} rootId
   * @constructor
   */
  function RowInfo($tr, $select, rootId) {
    var key = selectExtractKey($select);
    var $tdSelect = $select.parent();
    while ('TD' !== $tdSelect[0].tagName) {
      $tdSelect = $tdSelect.parent();
    }
    var $defaultOption = $select.children('option[value="default"]');
    var $disabledOption = $select.children('option[value="disabled"]');
    var hasDisabledOption = (1 === $disabledOption.length);
    if (hasDisabledOption) {
      var $inheritOption = $defaultOption;
    }
    else {
      $disabledOption = null;
      $inheritOption = null;
    }
    $.extend(this, {
      id: $tr.attr('id'),
      key: key,
      name: $select.attr('name'),
      parentId: extractParentId($tr, hasDisabledOption, rootId),
      $tr: $tr,
      $select: $select,
      $tdSelect: $tdSelect,
      hasDisabledOption: hasDisabledOption,
      $inheritOption: $inheritOption,
      $disabledOption: $disabledOption,
      $defaultOption: $defaultOption
    });
  }

  /**
   * Extract the plugin key from a select element.
   */
  var selectExtractKey = (function() {
    var prefix = 'crumbs_weights[rules.';
    var suffix = '][weight]';
    return function($select) {
      var nameAttribute = $select.attr('name');
      if (prefix === nameAttribute.substr(0, prefix.length)
        && suffix === nameAttribute.substr(nameAttribute.length - suffix.length)
      ) {
        return nameAttribute.substr(prefix.length, nameAttribute.length - suffix.length);
      }
      else {
        return null;
      }
    };
  })();

  /**
   * Determine the parent key for a table row.
   * A row with a default value ("disabled by default") does not have a parent.
   * The root wildcard row ("*") does not have a parent.
   *
   * @param {jQuery} $tr
   * @param {boolean} hasDisabledOption
   * @param {string} rootId
   * @returns {string}
   */
  function extractParentId($tr, hasDisabledOption, rootId) {
    if (!hasDisabledOption) {
      return null;
    }
    var classes = $($tr)[0].className.split(' ');
    for (var i = 0; i < classes.length; ++i) {
      if ('child-of-' === classes[i].substr(0, 9)) {
        return classes[i].substr(9);
      }
    }
    // The top-level rows don't have a child-of- class.
    var id = $tr.attr('id');
    if (rootId === id) {
      return null;
    }
    return rootId;
  }

  /**
   * Extract a property that exixsts in each row.
   * @param {{RowInfo}} rows
   * @param {string} x
   * @return {{}}
   */
  function rowsExtractX(rows, x) {
    var result = {};
    for (var key in rows) {
      result[key] = rows[key][x];
    }
    return result;
  }

  /**
   * Extract values from select elements in table rows.
   * @param {{RowInfo}} rows
   * @returns {{}}
   */
  function rowsExtractValues(rows) {
    var values = {};
    for (var key in rows) {
      values[key] = rows[key].$select.val();
    }
    return values;
  }

  /**
   *
   * @param rows
   */
  function rowsColorizeDisabledOptions(rows) {
    for (var key in rows) {
      if (rows[key].hasDisabledOption) {
        rows[key]['$disabledOption'].addClass('crumbs-option-disabled');
      }
      else {
        rows[key]['$defaultOption'].addClass('crumbs-option-disabled');
      }
    }
  }

  /**
   * Class to calculate inherited values. See construct() function.
   * @param values
   * @param hierarchy Hierarchy
   * @constructor
   */
  function InheritanceModel(values, hierarchy) {

    /**
     * Set a dropdown select value.
     * @param key
     * @param value
     */
    this.setValue = function(key, value) {
      if (values[key] === value) {
        return;
      }
      values[key] = value;
      var effectiveValue = ('default' === value)
        ? getInheritedValue(key)
        : value;
      if (effectiveValues[key] === effectiveValue) {
        notifyObservers(key);
      }
      else {
        updateEffectiveValues(key, effectiveValue);
      }
    };

    /**
     * Observe changes
     * @param {Drupal.crumbs.RowUpdaterInterface} observer
     */
    this.onEffectiveValueUpdate = function(observer) {
      observers.push(observer);
      for (var key in effectiveValues) {
        observer.updateRow(key, values[key], effectiveValues[key]);
      }
    };

    var effectiveValues = {};
    for (var rootKey in hierarchy.getRootKeys()) {
      if ('default' !== values[rootKey]) {
        initEffectiveValues(rootKey, values[rootKey]);
      }
      else {
        initEffectiveValues(rootKey, 'disabled');
      }
    }

    /**
     * @type {Drupal.crumbs.RowUpdaterInterface[]}
     */
    var observers = [];

    /**
     *
     * @param key
     */
    function notifyObservers(key) {
      for (var i = 0; i < observers.length; ++i) {
        observers[i].updateRow(key, values[key], effectiveValues[key]);
      }
    }

    /**
     * Initialize effective values for a row and its children.
     * @param parentKey
     * @param effectiveValue
     */
    function initEffectiveValues(parentKey, effectiveValue) {
      effectiveValues[parentKey] = effectiveValue;
      for (var key in hierarchy.getChildKeys(parentKey)) {
        if ('default' === values[key]) {
          initEffectiveValues(key, effectiveValue);
        }
        else {
          initEffectiveValues(key, values[key]);
        }
      }
    }

    /**
     * Update effective values for a row and its children.
     * @param parentKey
     * @param effectiveValue
     */
    function updateEffectiveValues(parentKey, effectiveValue) {
      effectiveValues[parentKey] = effectiveValue;
      notifyObservers(parentKey);
      for (var key in hierarchy.getChildKeys(parentKey)) {
        if ('default' === values[key] && effectiveValues[key] !== effectiveValue) {
          updateEffectiveValues(key, effectiveValue);
        }
      }
    }

    /**
     * Get inherited value for key.
     * @param key
     * @returns {*}
     */
    function getInheritedValue(key) {
      if (!hierarchy.hasParentKey(key)) {
        return 'disabled';
      }
      var parentKey = hierarchy.getParentKey(key);
      var parentValue = values[parentKey];
      return ('default' === parentValue)
        ? getInheritedValue(parentKey)
        : parentValue;
    }
  }

  /**
   * Class that represents a hierarchy from parent keys.
   * @param parentKeys
   * @constructor
   */
  function Hierarchy(parentKeys) {
    var hierarchy = {};
    var rootKeys = {};
    for (var key in parentKeys) {
      var parentKey = parentKeys[key];
      if ('string' === typeof parentKey) {
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
     *
     * @param parentKey
     * @returns {*}
     */
    this.getChildKeys = function(parentKey) {
      return (undefined !== hierarchy[parentKey])
        ? hierarchy[parentKey]
        : {};
    };

    /**
     *
     * @param key
     * @returns bool
     */
    this.hasParentKey = function(key) {
      return undefined !== parentKeys[key]
        && null !== parentKeys[key];
    };

    /**
     *
     * @param key
     * @returns {*}
     */
    this.getParentKey = function(key) {
      return (undefined !== parentKeys[key])
        ? parentKeys[key]
        : null;
    };

    /**
     * @returns {{}}
     */
    this.getRootKeys = function() {
      return rootKeys;
    };
  }

})(jQuery);