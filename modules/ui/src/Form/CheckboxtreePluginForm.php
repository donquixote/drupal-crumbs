<?php

namespace Drupal\crumbs_ui\Form;

use Drupal\crumbs\PluginSystem\PluginType\ParentPluginType;
use Drupal\crumbs\PluginSystem\PluginType\PluginTypeInterface;
use Drupal\crumbs\PluginSystem\PluginType\TitlePluginType;
use Drupal\crumbs_ui\FormElement\Theme\CheckboxTreeTable;
use Drupal\crumbs_ui\FormElement\WeightsCheckboxTree;

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

    $buffer = crumbs()->pluginDiscoveryBuffer;

    $tree = $buffer->getQualifiedTree($this->pluginType);

    $settings_key = $this->pluginType->getSettingsKey();
    $settings = variable_get($settings_key, array()) + array(
      'statuses' => array(),
      'weights' => array(),
    );

    $form[$settings_key] = array(
      '#title' => $this->isParentForm
        ? t('Criteria for parent-finding')
        : t('Criteria for title-finding'),
      '#type' => 'crumbs_ui_element',
      '#crumbs_ui_element_object' => new WeightsCheckboxTree($tree),
      '#crumbs_ui_theme_object' => new CheckboxTreeTable($tree),
      // Fetching the default value is not automated by system_settings_form().
      '#default_value' => array(
        'statuses' => $settings['statuses'],
        'weights' => $settings['weights'],
      ),
    );

    $form = system_settings_form($form);
    $form['#submit'][] = '_crumbs_admin_flush_cache';

    return $form;
  }
}
