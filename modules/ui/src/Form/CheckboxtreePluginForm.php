<?php

namespace Drupal\crumbs_ui\Form;

use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery;
use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;
use Drupal\crumbs_ui\FormElement\Theme\CheckboxTreeTable;
use Drupal\crumbs_ui\FormElement\WeightsCheckboxTree;
use Drupal\crumbs_ui\PluginKey\RawHierarchy;

class CheckboxtreePluginForm implements FormBuilderInterface {

  /**
   * @var \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface
   */
  protected $pluginType;

  /**
   * @var bool
   *   TRUE, if this is the form for parent-finding plugins.
   *   FALSE, if this is the form for title-finding plugins.
   */
  protected $isParentForm;

  /**
   * @return static
   */
  static function parentPluginForm() {
    return new static(new ParentPluginType());
  }

  /**
   * @return static
   */
  static function titlePluginForm() {
    return new static(new TitlePluginType());
  }

  /**
   * @param \Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface $pluginType
   */
  public function __construct(PluginTypeInterface $pluginType) {
    $this->pluginType = $pluginType;
    if ($pluginType instanceof ParentPluginType) {
      $this->isParentForm = TRUE;
    }
    elseif ($pluginType instanceof TitlePluginType) {
      $this->isParentForm = FALSE;
    }
    else {
      throw new \InvalidArgumentException("Plugin type not supported.");
    }
  }

  /**
   * @param array $form
   * @param array $form_state
   *
   * @return array
   *   The form array.
   */
  public function buildForm($form, $form_state) {

    $labeledPluginCollection = $this->loadLabeledPluginCollection();
    $settings_key = $this->pluginType->getSettingsKey();

    $descriptions = $labeledPluginCollection->getDescriptions();
    $leaves = $labeledPluginCollection->getLeaves();
    $raw_hierarchy = RawHierarchy::createFromKeys($descriptions + $leaves);

    $form[$settings_key] = array(
      '#title' => $this->isParentForm
        ? t('Criteria for parent-finding')
        : t('Criteria for title-finding'),
      '#type' => 'crumbs_ui_element',
      '#crumbs_ui_element_object' => new WeightsCheckboxTree($raw_hierarchy, $labeledPluginCollection),
      '#crumbs_ui_theme_object' => new CheckboxTreeTable($raw_hierarchy, $labeledPluginCollection),
      // Fetching the default value is not automated by system_settings_form().
      '#default_value' => variable_get($settings_key, array()) + array(
        'statuses' => array(),
        'weights' => array(),
      ),
    );

    $form[$settings_key]['#default_value']['statuses'] += $labeledPluginCollection->getDefaultStatuses();

    $form = system_settings_form($form);
    $form['#submit'][] = '_crumbs_admin_flush_cache';
    return $form;
  }

  /**
   * @return \Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection
   */
  protected function loadLabeledPluginCollection() {

    // Get the tree of plugin descriptions.
    $discovery = PluginDiscovery::create();
    $parentPluginCollection = new LabeledPluginCollection();
    $titlePluginCollection = new LabeledPluginCollection();
    $discovery->discoverPlugins($parentPluginCollection, $titlePluginCollection);

    if ($this->pluginType instanceof TitlePluginType) {
      return $titlePluginCollection;
    }

    if ($this->pluginType instanceof ParentPluginType) {
      return $parentPluginCollection;
    }

    throw new \InvalidArgumentException("Unsupported plugin type.");
  }
}
