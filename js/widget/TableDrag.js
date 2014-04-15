/*global jQuery, document, Drupal */

(function () {
  "use strict";
  var $ = jQuery;
  if (!Drupal.crumbs) Drupal.crumbs = {};

  /**
   * @param {table} table
   * @param {{}} tableSettings
   *
   * @constructor
   * @extends {Drupal.tableDrag}
   */
  Drupal.crumbs.TableDrag = function(table, tableSettings) {
    Drupal.tableDrag.call(this, table, tableSettings);
  };

  Drupal.crumbs.TableDrag.prototype = Object.create(Drupal.tableDrag.prototype);

  /**
   * Overrides the parent method, and removes unnecessary field handling.
   *
   * @param {{}} event
   * @param {Drupal.crumbs.TableDrag} self
   */
  Drupal.crumbs.TableDrag.prototype.dropRow = function(event, self) {
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
  Drupal.crumbs.TableDrag.prototype.restripeTable = function() {};
})();
