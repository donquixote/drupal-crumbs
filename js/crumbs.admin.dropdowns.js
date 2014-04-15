/*global jQuery, document, Drupal */

(function() {
  "use strict";
  var $ = jQuery;

  Drupal.behaviors.crumbsAdminDropdowns = {};

  /**
   * @param {jQuery} context
   * @param {{}} settings
   */
  Drupal.behaviors.crumbsAdminDropdowns.attach = function(context, settings) {
    $('table#crumbs_weights_dropdowns', context).once('crumbs-weights-dropdowns', function () {

      var $table = $(this);

      // The treeTable mechanics are quite independent from the rest..
      // $table.treeTable();

      /** @type {Drupal.crumbs.dropdowns.ServiceContainer} */
      var services = new Drupal.crumbs.dropdowns.ServiceContainer($table);

      services.initialDropdownTable().initTreeTableMechanics(
        services.treeExpandModel(),
        services.treeExpandVisibilityModel(),
        services.hierarchy());

      Drupal.crumbs.AdminTableRows.create(
        services.rowClassSwitcher(),
        services.focusGroupModel(),
        services.effectiveValueModel(),
        services.explicityModel(),
        services.hasExplicitChildrenModel(),
        services.hierarchy()
      );

      services.initialDropdownTable().rowStatusControls(
        services.masterStatusModel(),
        services.effectiveValueModel());

      services.initialDropdownTable().childrenColumn(services.hierarchy());

      services.initialDropdownTable().createWidget(
        services.focusGroupModel(),
        services.masterValueModel(),
        services.effectiveValueModel());

      // Spawn a drag area (starts empty).
      var $dragAreaContainer = $('<div>')
        .addClass('crumbs-drag-area-container')
        .insertAfter($table);

      Drupal.crumbs.DragArea.create(
        $dragAreaContainer,
        services.focusGroupModel(),
        services.masterValueModel());
    });
  };

})();
