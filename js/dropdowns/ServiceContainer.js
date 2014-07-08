/*global jQuery, document, Drupal */

(function () {
  "use strict";
  if (!Drupal.crumbs) Drupal.crumbs = {};
  if (!Drupal.crumbs.dropdowns) Drupal.crumbs.dropdowns = {};

  /**
   * @param {function} factory
   * @returns {function}
   */
  function cache(factory) {
    var service = null;

    return function () {
      if (service) {
        return service;
      }
      return service = factory();
    }
  }

  /**
   * @param {jQuery} $table
   *
   * @constructor
   */
  Drupal.crumbs.dropdowns.ServiceContainer = function($table) {

    /**
     * @type {Drupal.crumbs.dropdowns.ServiceContainer}
     */
    var services = this;

    /**
     * @returns {Drupal.crumbs.InitialDropdownTable}
     */
    this.initialDropdownTable = cache(function() {
      return new Drupal.crumbs.InitialDropdownTable($table);
    });

    /**
     * @returns {Drupal.crumbs.RowClassSwitcher}
     */
    this.rowClassSwitcher = cache(function() {
      var rows = services.initialDropdownTable().getRows();
      return new Drupal.crumbs.RowClassSwitcher(rows);
    });

    /**
     * @returns {Drupal.crumbs.HierarchyExtractor}
     */
    this.hierarchyExtractor = cache(function() {
      var keys = services.initialDropdownTable().getKeys();
      var rootKeys = services.initialDropdownTable().getRootKeys();
      return new Drupal.crumbs.HierarchyExtractor(keys, rootKeys);
    });

    /**
     * @returns {Drupal.crumbs.Hierarchy}
     */
    this.hierarchy = cache(function() {
      return services.hierarchyExtractor().buildHierarchy();
    });

    /**
     * @returns {Drupal.crumbs.TreeExpandModel}
     */
    this.treeExpandModel = cache(function() {
      return Drupal.crumbs.TreeExpandModel.create(services.hierarchy());
    });

    /**
     * @returns {Drupal.crumbs.TreeExpandVisibilityModel}
     */
    this.treeExpandVisibilityModel = cache(function() {
      var model = new Drupal.crumbs.TreeExpandVisibilityModel(services.hierarchy(), services.treeExpandModel());
      services.treeExpandModel().observeExpandedKeys(model);
      return model;
    });

    /**
     * @returns {Drupal.crumbs.MasterValueModel}
     */
    this.masterValueModel = cache(function() {
      var initialValues = services.initialDropdownTable().getInitialValues();
      return new Drupal.crumbs.MasterValueModel(initialValues);
    });

    /**
     * @returns {Drupal.crumbs.ExplicityModel}
     */
    this.explicityModel = cache(function() {
      var hardKeys = services.initialDropdownTable().getRootKeys();
      var explicityModel = new Drupal.crumbs.ExplicityModel(hardKeys);
      services.masterValueModel().observeMasterValues(explicityModel);
      return explicityModel;
    });

    /**
     * @returns {Drupal.crumbs.InheritanceModel}
     */
    this.inheritanceModel = cache(function() {
      var inheritanceModel = new Drupal.crumbs.InheritanceModel(services.hierarchy(), services.explicityModel());
      services.explicityModel().observeExplicity(inheritanceModel);
      return inheritanceModel;
    });

    /**
     * @returns {Drupal.crumbs.HasExplicitChildrenModel}
     */
    this.hasExplicitChildrenModel = cache(function() {
      var hasExplicitChildrenModel = new Drupal.crumbs.HasExplicitChildrenModel(services.hierarchy(), services.explicityModel());
      services.explicityModel().observeExplicity(hasExplicitChildrenModel);
      return hasExplicitChildrenModel;
    });

    /**
     * @returns {Drupal.crumbs.EffectiveValueModel}
     */
    this.effectiveValueModel = cache(function() {
      return Drupal.crumbs.EffectiveValueModel.createInstance(
        services.masterValueModel(),
        services.inheritanceModel());
    });

    /**
     * @returns {Drupal.crumbs.MasterStatusModel}
     */
    this.masterStatusModel = cache(function(){
      var masterStatusModel = new Drupal.crumbs.MasterStatusModel(
        services.masterValueModel(),
        services.effectiveValueModel(),
        services.explicityModel());
      services.masterValueModel().observeMasterValues(masterStatusModel);
      return masterStatusModel;
    });

    /**
     * @returns {Drupal.crumbs.FocusGroupModel}
     */
    this.focusGroupModel = cache(function() {
      return Drupal.crumbs.FocusGroupModel.createInstance(services.inheritanceModel());
    });
  };

})();
