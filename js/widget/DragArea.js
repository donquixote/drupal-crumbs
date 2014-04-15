/*global jQuery, document, Drupal */

(function() {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @interface
   */
  Drupal.crumbs.DragAreaObserverInterface = function() {};

  /**
   * @param {string[]} keysSorted
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.reorder = function(keysSorted) {};

  /**
   * @param {string} key
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.drag = function(key) {};

  /**
   * @param {string} key
   */
  Drupal.crumbs.DragAreaObserverInterface.prototype.drop = function(key) {};

  /**
   * @param {jQuery} $tbody
   * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
   *
   * @constructor
   * @implements {Drupal.crumbs.MasterValueObserverInterface}
   * @implements {Drupal.crumbs.FocusGroupObserverInterface}
   */
  Drupal.crumbs.DragArea = function($tbody, focusGroupModel) {

    var $table = $tbody.parent();
    $table.css('width', 'auto');

    /**
     * @type {Drupal.crumbs.DragAreaObserverInterface[]}
     */
    var observers = [];

    /**
     * @type {Object.<DragAreaRow>}
     */
    var rows = {};

    /**
     * @type {Drupal.crumbs.TableDrag}
     */
    var drupalTableDrag = new Drupal.crumbs.TableDrag($table[0], {});

    /**
     * Gets the row keys in the current DOM order of the tabledrag rows.
     *
     * @returns {string[]}
     */
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
     * @param {string} newKey
     * @param {int} weight
     * @returns {DragAreaRow}
     */
    function createInsertRow(newKey, weight) {
      var $tr = $('<tr>').addClass('draggable').append('<td>');
      if (focusGroupModel.isKeyInFocusGroup(newKey)) {
        $tr.addClass('crumbs-focus-group');
      }
      $tr.mousedown(function(){
        focusGroupModel.focusGroupWithKey(newKey);
      });
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
    function setKey(newKey, weight) {
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
        newRow.$tr.removeClass('odd even');
      }
    }

    /**
     * @param {string} key
     */
    function removeKey(key) {
      if (rows[key]) {
        rows[key].$tr.remove();
        delete rows[key];
      }
    }

    /**
     * Registers an observer to be notified when the order of rows changes.
     *
     * @param {Drupal.crumbs.DragAreaObserverInterface} observer
     */
    this.observeDragArea = function(observer) {
      observers.push(observer);
    };

    /**
     * @param {Object.<int|'disabled'|'default'>} masterValues
     *   The complete set of master values.
     */
    this.updateMasterValues = function(masterValues) {
      for (var key in masterValues) {
        var value = masterValues[key];
        if ('' + parseInt(value) === '' + value) {
          // It's a numeric weight.
          setKey(key, parseInt(value));
        }
        else {
          // It is default or inherit.
          removeKey(key);
        }
      }
    };

    /**
     * @param {string} key
     */
    this.focusAddKey = function (key) {
      if (rows[key]) {
        rows[key].$tr.addClass('crumbs-focus-group');
      }
    };

    /**
     * @param {string} key
     */
    this.focusRemoveKey = function (key) {
      if (rows[key]) {
        rows[key].$tr.removeClass('crumbs-focus-group');
      }
    };
  };

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
   * @param {jQuery} $dragAreaContainer
   * @param {Drupal.crumbs.FocusGroupModel} focusGroupModel
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @returns {Drupal.crumbs.DragArea}
   */
  Drupal.crumbs.DragArea.create = function($dragAreaContainer, focusGroupModel, masterValueModel) {
    var $dragAreaTable = $('<table>').appendTo($dragAreaContainer);
    var $dragAreaTbody = $('<tbody>').appendTo($dragAreaTable);
    var dragArea = new Drupal.crumbs.DragArea($dragAreaTbody, focusGroupModel);
    focusGroupModel.observeFocusGroup(dragArea);

    dragArea.observeDragArea(new DragAreaObserver(masterValueModel));
    masterValueModel.observeMasterValues(dragArea);

    return dragArea;
  };

  /**
   * @param {Drupal.crumbs.MasterValueModel} masterValueModel
   * @constructor
   * @implements {Drupal.crumbs.DragAreaObserverInterface}
   */
  function DragAreaObserver(masterValueModel) {

    /**
     * @param {string[]} keysSorted
     */
    this.reorder = function(keysSorted) {
      var weights = {};
      for (var i = 0; i < keysSorted.length; ++i) {
        var weight = i + 1;
        weights[keysSorted[i]] = weight;
      }
      masterValueModel.setMasterValues(weights);
    };

    this.drag = function(){};

    this.drop = function(){};
  }

})();
