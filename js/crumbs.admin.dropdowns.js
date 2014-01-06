/*global jQuery, document, Drupal */

(function() {
  "use strict";

  var $ = jQuery;

  Drupal.behaviors.crumbsAdminDropdowns = {
    attach: function (context, settings) {
      //noinspection JSUnresolvedFunction
      $('table#crumbs_weights_dropdowns', context).once('crumbs-weights-dropdowns', function () {
        var $table = $(this);
        $table.treeTable();
        tableInsertColumn($table, 2, 'crumbs-column-weight', 'Weight');
        tableInsertColumn($table, 1, 'crumbs-column-children', '');
        var rootId = 'crumbs-rule-3389dae361af79b04c9c8e7057f60cc6';
        var rows = extractRows($table, rootId);
        rowsColorizeDisabledOptions(rows);
        var values = rowsExtractValues(rows);
        var selectElements = rowsExtractX(rows, '$select');
        var inheritanceParentKeys = rowsExtractX(rows, 'inheritanceParentKey');
        var inheritanceHierarchy = new Hierarchy(inheritanceParentKeys);
        var parentKeys = rowsExtractX(rows, 'parentKey');
        var hierarchy = new Hierarchy(parentKeys);
        var inheritanceModel = new InheritanceModel(values, inheritanceHierarchy);
        var trElements = rowsExtractX(rows, '$tr');
        var inheritOptionElements = rowsExtractX(rows, '$inheritOption');
        inheritanceModel.onEffectiveValueUpdate(new SelectOptionUpdater(inheritOptionElements, inheritanceHierarchy));
        inheritanceModel.onEffectiveValueUpdate(new TransparencyUpdater(trElements, inheritanceHierarchy));
        inheritanceModel.onEffectiveValueUpdate(new BackgroundUpdater(trElements));

        var rowWidgets = rowsWidgetize(rows);
        tableBindEvents(selectElements, rowWidgets, inheritanceModel);

        var $dragAreaContainer = $('<div>').addClass('crumbs-drag-area-container').insertBefore($table);
        var dragArea = createDragArea($dragAreaContainer, values);
        dragArea.onReorder(new DragAreaObserver(selectElements, rowWidgets, inheritanceModel));
        dragAreaBindEvents(selectElements, rowWidgets, dragArea);

        var hasOverrideModel = new HasOverrideModel(hierarchy, inheritanceHierarchy);
        hasOverrideModel.onHasOverride = function(key, hasOverride) {
          if (hasOverride) {
            rows[key].$tr.addClass('crumbs-has-override');
            rows[key].$tdChildren.html('<div>..');
          }
          else {
            rows[key].$tr.removeClass('crumbs-has-override');
            rows[key].$tdChildren.html('');
          }
        };
        inheritanceModel.onEffectiveValueUpdateNoInit(new HasOverrideModelUpdater(hasOverrideModel));
        hasOverrideModel.setValues(values);

        syncFocus(trElements, dragArea, hierarchy);
      });
    }
  };

  /**
   * @param {HasOverrideModel} hasOverrideModel
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function HasOverrideModelUpdater(hasOverrideModel) {
    this.updateRow = function(key, value, effectiveValue) {
      hasOverrideModel.setValue(key, value);
    };
  }

  /**
   * @param {{jQuery}} selectElements
   * @param {{RowWidget}} rowWidgets
   * @param {DragArea} dragArea
   */
  function dragAreaBindEvents(selectElements, rowWidgets, dragArea) {
    for (var key in selectElements) {
      dragRowBindEvents(key, selectElements[key], rowWidgets[key], dragArea);
    }
  }

  /**
   * @param {string} key
   * @param {jQuery} $select
   * @param {RowWidget} rowWidget
   * @param {DragArea} dragArea
   */
  function dragRowBindEvents(key, $select, rowWidget, dragArea) {

    function setValue(value) {
      if (isNumber(value)) {
        dragArea.setKey(key, value);
      }
      else {
        dragArea.removeKey(key);
      }
    }

    $select.change(function(){
      var value = $select.val();
      setValue(value);
    });

    rowWidget.onValueUpdate(setValue, false);
  }

  /**
   * @param {jQuery} $container
   * @param {{}} values
   * @returns {DragArea}
   */
  function createDragArea($container, values) {
    var $dragAreaTable = $('<table>').appendTo($container);
    var $dragAreaTbody = $('<tbody>').appendTo($dragAreaTable);
    var dragArea = new DragArea($dragAreaTbody);
    for (var k in values) {
      if (isNumber(values[k])) {
        dragArea.setKey(k, values[k]);
      }
    }
    return dragArea;
  }

  /**
   * @param {{jQuery}} selectElements
   * @param {{RowWidget}} rowWidgets
   * @param {InheritanceModel} inheritanceModel
   */
  function tableBindEvents(selectElements, rowWidgets, inheritanceModel) {
    for (var key in selectElements) {
      rowBindEvents(key, selectElements[key], rowWidgets[key], inheritanceModel);
    }
    inheritanceModel.onEffectiveValueUpdate(new WidgetUpdater(rowWidgets));
  }

  /**
   * @param {string} key
   * @param {jQuery} $select
   * @param {RowWidget} rowWidget
   * @param {InheritanceModel} inheritanceModel
   */
  function rowBindEvents(key, $select, rowWidget, inheritanceModel) {

    function updateFromSelect() {
      var value = $select.val();
      rowWidget.setValue(value);
      inheritanceModel.setValue(key, value);
    }
    updateFromSelect();

    $select.change(updateFromSelect);

    rowWidget.onValueUpdate(function(value) {
      inheritanceModel.setValue(key, value);
      $select.val(value);
    }, false);
  }

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
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function RowUpdater() {}

  RowUpdater.prototype.updateRow = function (key, value, effectiveValue) {};

  /**
   * @param {{jQuery}} selectElements
   * @param {{RowWidget}} rowWidgets
   * @param {InheritanceModel} inheritanceModel
   * @constructor
   * @implements Drupal.crumbs.DragAreaObserverInterface
   */
  function DragAreaObserver(selectElements, rowWidgets, inheritanceModel) {
    /**
     * @param {string[]} keysSorted
     */
    this.reorder = function(keysSorted) {
      for (var i = 0; i < keysSorted.length; ++i) {
        var key = keysSorted[i];
        var $select = selectElements[key];
        var rowWidget = rowWidgets[key];
        $select.val(i);
        rowWidget.setValue(i);
        inheritanceModel.setValue(key, i);
      }
    };
    this.drag = function(){};
    this.drop = function(){};
  }

  /**
   * @param {{RowWidget}} rowWidgets
   * @constructor
   * @implements {Drupal.crumbs.RowUpdaterInterface}
   */
  function WidgetUpdater(rowWidgets) {
    this.updateRow = function(key, value, effectiveValue) {
      if (isNumber(effectiveValue)) {
        rowWidgets[key].setEffectiveWeight(effectiveValue);
      }
    };
  }

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
      row.inheritanceParentKey = (null !== row.inheritanceParentId)
        ? rowsById[row.inheritanceParentId].key
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
    $.extend(this, {
      id: $tr.attr('id'),
      key: key,
      name: $select.attr('name'),
      $tr: $tr,
      $inheritOption: $inheritOption,
      $disabledOption: $disabledOption,
      $defaultOption: $defaultOption
    });
    this.$select = $select;
    this.parentId = extractParentId($tr, rootId);
    this.inheritanceParentId = this.hasDisabledOption ? this.parentId : null;
    this.$tdWeight = $('.crumbs-column-weight', $tr);
    this.$tdChildren = $('.crumbs-column-children', $tr);
  }

  /**
   * Extract the plugin key from a select element.
   */
  var selectExtractKey = (function() {
    var prefix = 'crumbs_weights[rules.';
    var suffix = '][weight]';
    return function($select) {
      var nameAttribute = $select.attr('name');
      var suffixOffset = nameAttribute.length - suffix.length;
      var keyLength = nameAttribute.length - prefix.length - suffix.length;
      if (prefix === nameAttribute.substr(0, prefix.length)
        && suffix === nameAttribute.substr(suffixOffset)
      ) {
        return nameAttribute.substr(prefix.length, keyLength);
      }
      else {
        return null;
      }
    };
  })();

  /**
   * @param {jQuery} $table
   * @param {int} index
   * @param {string} className
   * @param {string} title
   * @return {CellInRow[]}
   */
  function tableInsertColumn($table, index, className, title) {
    var result = [];
    $table.children().children('tr').each(function(){
      var $tr = $(this);
      //noinspection JSValidateTypes
      var tdPos = $tr.children('td, th')[index];
      if (!tdPos) {
        return;
      }
      var $tdCreated = $('<' + tdPos.tagName + '>').addClass(className);
      $tdCreated.insertBefore(tdPos);
      if ('TH' === tdPos.tagName) {
        // $tdCreated.prev().html('Status');
        $tdCreated.html(title);
      }
      var id = $tr.attr('id');
      result.push(new CellInRow($tr, $tdCreated));
    });
    return result;
  }

  /**
   * @param {jQuery} $td
   * @param {jQuery} $tr
   * @constructor
   */
  function CellInRow($td, $tr) {
    /** @type {jQuery} */
    this.$td = $td;
    /** @type {jQuery} */
    this.$tr = $tr;
  }

  /**
   * Determine the parent key for a table row.
   * A row with a default value ("disabled by default") does not have a parent.
   * The root wildcard row ("*") does not have a parent.
   *
   * @param {jQuery} $tr
   * @param {string} rootId
   * @returns {string|null}
   */
  function extractParentId($tr, rootId) {
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
   * @param {{RowInfo}} rows
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
   * @param {{RowInfo}} rows
   * @return {{RowWidget}}
   */
  function rowsWidgetize(rows) {
    var rowWidgets = {};
    for (var key in rows) {
      rowWidgets[key] = rowWidgetize(rows[key]);
    }
    return rowWidgets;
  }

  /**
   * @param {RowInfo} row
   * @return {RowWidget}
   */
  function rowWidgetize(row) {

    row.$select.hide();
    var $selectStatus = $('<select>').appendTo(row.$tdSelect);
    if (row.hasDisabledOption) {
      if (row.inheritanceParentId) {
        $('<option>').val('default').html('Inherit').addClass('crumbs-option-inherit').appendTo($selectStatus);
      }
      $('<option>').val('disabled').html('Disabled').addClass('crumbs-option-disabled').appendTo($selectStatus);
      $('<option>').val('enabled').html('Enabled').addClass('crumbs-option-enabled').appendTo($selectStatus);
    }
    else {
      $('<option>').val('default').html('Disabled by default').addClass('crumbs-option-disabled').appendTo($selectStatus);
      $('<option>').val('enabled').html('Enabled').addClass('crumbs-option-enabled').appendTo($selectStatus);
    }
    var $divWeight = $('<div>').addClass('crumbs-div-weight').appendTo(row.$tdWeight);
    return new RowWidget($selectStatus, $divWeight, null !== row.inheritanceParentId);
  }

  /**
   * @param {jQuery} $selectStatus
   * @param {jQuery} $divWeight
   * @param {boolean} canInherit
   * @constructor
   */
  function RowWidget($selectStatus, $divWeight, canInherit) {

    var weight = -1;
    function setWeight(newWeight) {
      weight = newWeight;
      $divWeight.html(weight);
    }

    /**
     * @type {function[]}
     */
    var observers = [];

    this.setValue = function(value) {
      switch (value) {
        case 'disabled':
          $selectStatus.val('disabled');
          break;
        case 'default':
          $selectStatus.val('default');
          break;
        default:
          $selectStatus.val('enabled');
          setWeight(value);
      }
    };

    /**
     * @param {int} effectiveWeight
     */
    this.setEffectiveWeight = function(effectiveWeight) {
      if (canInherit) {
        switch (getValue()) {
          case 'default':
          case 'disabled':
            setWeight(effectiveWeight);
            break;
          default:
            break;
        }
      }
    };

    /**
     * @param {function} observer
     * @param {boolean} doInit
     */
    this.onValueUpdate = function(observer, doInit) {
      observers.push(observer);
      if (doInit) {
        observer(getValue());
      }
    };

    /**
     * @returns {*}
     */
    function getValue() {
      var status = $selectStatus.val();
      switch (status) {
        case 'enabled':
          return weight;

        case 'disabled':
          return 'disabled';

        case 'default':
        default:
          return 'default';
      }
    }

    function update() {
      var value = getValue();
      for (var i = 0; i < observers.length; ++i) {
        observers[i](value);
      }
    }

    $selectStatus.change(update);
  }

  /**
   * Class to determine which keys have any ancestors with overridden values.
   * @param {Hierarchy} hierarchy
   *   Collapse / expand hierarchy.
   * @param {Hierarchy} inheritanceHierarchy
   * @constructor
   */
  function HasOverrideModel(hierarchy, inheritanceHierarchy) {

    var self = this;

    /**
     * @type {{bool}}
     */
    var explicitKeys = {};

    /**
     * @type {{bool}}
     */
    var hasOverrides = {};

    function recalculateAll() {
      for (var rootKey in hierarchy.getRootKeys()) {
        recalculateKey(rootKey);
      }
    }

    /**
     * @param {string} key
     * @returns {boolean}
     */
    function recalculateKey(key) {
      var hasOverride = false;
      for (var childKey in hierarchy.getChildKeys(key)) {
        if (recalculateKey(childKey)) {
          hasOverride = true;
        }
      }
      setHasOverride(key, hasOverride);
      return hasOverride || explicitKeys[key];
    }

    /**
     * @param {string} key
     * @param {boolean} hasOverride
     */
    function setHasOverride(key, hasOverride) {
      if (hasOverrides[key] !== hasOverride) {
        self.onHasOverride(key, hasOverride);
        hasOverrides[key] = hasOverride;
      }
    }

    /**
     * @param {string} key
     * @param {*} value
     */
    this.setValue = function(key, value) {
      var isExplicit = !inheritanceHierarchy.hasParentKey(key) || 'default' !== value;
      if (explicitKeys[key] !== isExplicit) {
        explicitKeys[key] = isExplicit;
        recalculateAll();
      }
    };

    /**
     * @param {{}} newValues
     */
    this.setValues = function(newValues) {
      for (var key in newValues) {
        explicitKeys[key] = !inheritanceHierarchy.hasParentKey(key) || 'default' !== newValues[key];
      }
      recalculateAll();
    };
  }

  /**
   * Event handler to be overridden.
   * @param key
   * @param hasOverride
   */
  HasOverrideModel.prototype.onHasOverride = function(key, hasOverride) {};

  /**
   * Class to calculate inherited values.
   * @param {{}} values
   * @param {Hierarchy} hierarchy
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

    /**
     * Observe changes
     * @param {Drupal.crumbs.RowUpdaterInterface} observer
     */
    this.onEffectiveValueUpdateNoInit = function(observer) {
      observers.push(observer);
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

  /**
   * See http://stackoverflow.com/a/1830844/246724
   * @param {string|int} n
   * @returns {boolean}
   */
  function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
  }

  /**
   * @interface
   */
  Drupal.crumbs.DragAreaObserverInterface = function() {};

  /**
   * @param {string[]} keys
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.reorder = function(keys) {};

  /**
   * @param {string} key
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.drag = function(key) {};

  /**
   * @param {string} key
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.drop = function(key) {};

  /**
   * @param {{}} table
   * @param {{}} tableSettings
   * @constructor
   * @extends {Drupal.tableDrag}
   */
  function TableDrag(table, tableSettings) {
    Drupal.tableDrag.call(this, table, tableSettings);
  }
  //noinspection JSPotentiallyInvalidConstructorUsage
  TableDrag.prototype = Object.create(Drupal.tableDrag.prototype);

  /**
   * @param {jQuery} $tbody
   * @constructor
   */
  function DragArea($tbody) {

    var self = this;

    var $table = $tbody.parent();
    $table.css('width', 'auto');

    /**
     * @param {string} key
     * @param {jQuery} $tr
     * @param {function} onFocusChange
     */
    function rowAddFocusListeners(key, $tr, onFocusChange) {
      var $spanExpander = $tr.find('span.expander');
      $tr.mousedown(function(){
        onFocusChange(key);
      });
      $tr.find('.handle').mousedown(function(){
        onFocusChange(key);
      });
    }

    var focusableRows = new FocusableRows(rowAddFocusListeners);

    //noinspection JSPotentiallyInvalidConstructorUsage
    /**
     * @type {TableDrag}
     */
    var drupalTableDrag = new TableDrag($table[0], {});

    function getKeysSorted() {
      var keysSorted = [];
      $tbody.children('tr').each(function(i, tr){
        var key = tr.crumbsRowKey;
        rows[key].weight = i;
        keysSorted.push(key);
      });
      return keysSorted;
    }

    drupalTableDrag.onDrop = function() {
      var keysSorted = getKeysSorted();
      var key = drupalTableDrag.rowObject.element.crumbsRowKey;
      for (var i = 0; i < observers.length; ++i) {
        observers[i].reorder(keysSorted);
        observers[i].drop(key);
      }
    };

    drupalTableDrag.onDrag = function() {
      var key = drupalTableDrag.rowObject.element.crumbsRowKey;
      for (var i = 0; i < observers.length; ++i) {
        observers[i].drag(key);
      }
    };

    /**
     * @type {Drupal.crumbs.DragAreaObserverInterface[]}
     */
    var observers = [];

    /**
     * @type {{DragAreaRow}}
     */
    var rows = {};

    /**
     * @param {string} newKey
     * @param {int} weight
     * @returns {DragAreaRow}
     */
    function createInsertRow(newKey, weight) {
      var $tr = $('<tr>').addClass('draggable').append('<td>');
      $tr[0].crumbsRowKey = newKey;
      var $td = $('<td>').html(newKey).appendTo($tr);
      return new DragAreaRow(newKey, weight, $tr, $td);
    }

    /**
     * @param {int} weight
     * @returns {DragAreaRow}
     */
    function findInsertPosition(weight) {
      weight = parseInt(weight);
      var next = null;
      for (var key in rows) {
        var rowWeight = parseInt(rows[key].weight);
        if (rowWeight > weight) {
          if (!next || parseInt(next.weight) > rowWeight) {
            next = rows[key];
          }
        }
      }
      return next;
    }


    /**
     * @param {string} newKey
     * @param {int} weight
     */
    this.setKey = function(newKey, weight) {
      var exists = true;
      if (!rows[newKey]) {
        exists = false;
        rows[newKey] = createInsertRow(newKey, weight);
      }
      var newRow = rows[newKey];
      var next = findInsertPosition(weight);
      if (next) {
        next.$tr.before(newRow.$tr);
      }
      else {
        $tbody.append(newRow.$tr);
      }
      if (!exists) {
        drupalTableDrag.makeDraggable(newRow.$tr[0]);
        focusableRows.addRow(newKey, newRow.$tr);
        newRow.$tr.removeClass('odd even');
      }
    };

    /**
     * @param {string} key
     */
    this.removeKey = function(key) {
      focusableRows.removeRow(key);
      if (rows[key]) {
        rows[key].$tr.remove();
        delete rows[key];
      }
    };

    /**
     * @type {FocusableRows}
     */
    this.focusableRows = focusableRows;

    /**
     * @param {Drupal.crumbs.DragAreaObserverInterface} observer
     */
    this.onReorder = function(observer) {
      observers.push(observer);
    };
  }

  /**
   * @param {string} newFocusKey
   */
  DragArea.prototype.onFocusChange = function(newFocusKey) {};

  /**
   * Overrides the parent method, and removes unnecessary field handling.
   *
   * @param {{}} event
   * @param {TableDrag} self
   */
  TableDrag.prototype.dropRow = function(event, self) {
    // Drop row functionality shared between mouseup and blur events.
    if (self.rowObject !== null) {
      var droppedRow = self.rowObject.element;
      // The row is already in the right place so we just release it.
      if (self.rowObject.changed === true) {

        self.rowObject.markChanged();
        if (self.changed === false) {
          self.changed = true;
        }
      }

      if (self.indentEnabled) {
        self.rowObject.removeIndentClasses();
      }
      if (self.oldRowElement) {
        $(self.oldRowElement).removeClass('drag-previous');
      }
      $(droppedRow).removeClass('drag').addClass('drag-previous');
      self.oldRowElement = droppedRow;
      self.onDrop();
      self.rowObject = null;
    }

    // Functionality specific only to mouseup event.
    if (self.dragObject !== null) {
      $('.tabledrag-handle', droppedRow).removeClass('tabledrag-handle-hover');

      self.dragObject = null;
      $('body').removeClass('drag');
      clearInterval(self.scrollInterval);
    }
  };

  /**
   * Don't restripe anything.
   */
  TableDrag.prototype.restripeTable = function() {};

  /**
   * @param {string} key
   * @param {int} weight
   * @param {jQuery} $tr
   * @param {jQuery} $td
   * @constructor
   */
  function DragAreaRow(key, weight, $tr, $td) {
    /** @type {string} */
    this.key = key;
    /** @type {int} */
    this.weight = weight;
    /** @type {jQuery} */
    this.$tr = $tr;
    /** @type {jQuery} */
    this.$td = $td;
  }

  /**
   * @param {{jQuery}} trElements
   * @param {DragArea} dragArea
   * @param {Hierarchy} hierarchy
   */
  function syncFocus(trElements, dragArea, hierarchy) {

    var sync = new FocusSync();

    /**
     * @param {string} key
     * @param {jQuery} $tr
     * @param {function} onFocusChange
     */
    function rowAddListeners(key, $tr, onFocusChange) {
      var $spanExpander = $tr.find('span.expander');
      $tr.mousedown(function(evt){
        if ($(evt.target).is('.expander, select')) {
          return;
        }
        onFocusChange(key);
      });
    }

    var rows = new FocusableRows(rowAddListeners);
    for (var key in trElements) {
      rows.addRow(key, trElements[key]);
    }
    sync.add(rows);

    sync.add(dragArea.focusableRows);

    sync.add(new FocusableHierarchy(hierarchy, trElements));
  }

  /**
   * @constructor
   */
  function FocusSync() {

    /**
     * @type {string}
     */
    var keyInFocus;

    /**
     * @type {Drupal.crumbs.FocusableRowsInterface[]}
     */
    var sets = [];

    /**
     * @param {Drupal.crumbs.FocusableRowsInterface} focusableRows
     */
    this.add = function(focusableRows) {
      var index = sets.length;
      sets.push(focusableRows);
      focusableRows.onFocusChange = function(newFocusKey) {
        if (keyInFocus !== newFocusKey) {
          for (var i = 0; i < sets.length; ++i) {
            // Don't self-update.
            if (i !== index || true) {
              sets[i].setFocusKey(newFocusKey);
            }
          }
          keyInFocus = newFocusKey;
        }
      }
    };
  }

  /**
   * @interface
   */
  Drupal.crumbs.FocusableRowsInterface = function() {};

  /**
   * @param {string} newKey
   */
  Drupal.crumbs.FocusableRowsInterface.prototype.setFocusKey = function(newKey) {};

  /**
   * @param {string} newFocusKey
   */
  Drupal.crumbs.FocusableRowsInterface.prototype.onFocusChange = function(newFocusKey) {};

  /**
   * @param {Hierarchy} hierarchy
   * @param {{jQuery}} trElements
   * @constructor
   * @implements {Drupal.crumbs.FocusableRowsInterface}
   */
  function FocusableHierarchy(hierarchy, trElements) {

    /**
     * @type {string}
     */
    var focusKey;

    /**
     * @param {string} key
     * @param {string} method
     *   Either 'addClass' or 'removeClass'
     */
    function modifyParents(key, method) {
      var parentKey = key;
      while (true) {
        parentKey = hierarchy.getParentKey(parentKey);
        if (!parentKey) {
          break;
        }
        var $trParent = trElements[parentKey];
        $trParent[method]('crumbs-has-child-with-focus');
      }
    }

    /**
     * @param {string} key
     * @param {string} method
     *   Either 'addClass' or 'removeClass'
     */
    function modifyChildren(key, method) {
      var childKeys = hierarchy.getChildKeys(key);
      for (var childKey in childKeys) {
        var $trChild = trElements[childKey];
        $trChild[method]('crumbs-has-parent-with-focus');
        modifyChildren(childKey, method);
      }
    }

    /**
     * @param {string} newKey
     */
    this.setFocusKey = function(newKey) {
      if (focusKey) {
        modifyParents(focusKey, 'removeClass');
        modifyChildren(focusKey, 'removeClass');
      }
      modifyParents(newKey, 'addClass');
      modifyChildren(newKey, 'addClass');
      focusKey = newKey;
    };
  }

  /**
   * @param {string} newFocusKey
   */
  FocusableHierarchy.prototype.onFocusChange = function(newFocusKey) {};

  /**
   * @param {function} [rowAddListeners]
   * @constructor
   * @implements {Drupal.crumbs.FocusableRowsInterface}
   */
  function FocusableRows(rowAddListeners) {

    if (undefined === rowAddListeners) {
      /**
       * @param {string} key
       * @param {jQuery} $tr
       * @param {function} onFocusChange
       */
      rowAddListeners = function(key, $tr, onFocusChange) {
        $tr.mousedown(function(){
          onFocusChange(key);
        });
      }
    }

    var self = this;

    /**
     * @type {string}
     */
    var focusKey;

    /**
     * @type {{jQuery}}
     */
    var trElements = {};

    /**
     * @param {string} newKey
     */
    this.setFocusKey = function(newKey) {
      if (newKey === focusKey) {
        return;
      }
      if (focusKey && trElements[focusKey]) {
        trElements[focusKey].removeClass('crumbs-row-focus');
      }
      if (trElements[newKey]) {
        trElements[newKey].addClass('crumbs-row-focus');
      }
      focusKey = newKey;
    };

    /**
     * @param {string} key
     * @param {jQuery} $tr
     */
    this.addRow = function(key, $tr) {
      if (trElements[key]) {
        throw "Table row with key " + key + " already exists.";
      }
      trElements[key] = $tr;
      rowAddListeners(key, $tr, function(key){
        self.onFocusChange(key);
      });
      if (key === focusKey) {
        $tr.addClass('crumbs-row-focus');
      }
    };

    /**
     * @param {string} key
     */
    this.removeRow = function(key) {
      delete trElements[key];
    }
  }

  /**
   * @param {string} newFocusKey
   */
  FocusableRows.prototype.onFocusChange = function(newFocusKey) {};

})();