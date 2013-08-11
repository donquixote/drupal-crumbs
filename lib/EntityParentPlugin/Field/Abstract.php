<?php

abstract class crumbs_EntityParentPlugin_Field_Abstract implements crumbs_EntityParentPlugin {

  /**
   * @var string
   */
  protected $fieldKey;

  /**
   * @var array
   */
  protected $bundlesByType;

  /**
   * @param string $field_key
   * @param array $bundles_by_type
   */
  function __construct($field_key, array $bundles_by_type) {
    $this->fieldKey = $field_key;
    $this->bundlesByType = $bundles_by_type;
  }

  /**
   * @inheritdoc
   */
  function describe($api, $entity_type, $keys) {
    if (!empty($this->bundlesByType[$entity_type])) {
      if ('user' === $entity_type) {
        $instance = field_info_instance('user', $this->fieldKey, 'user');
        foreach ($keys as $key => $title) {
          $api->addRule($key, $title);
        }
        $api->descWithLabel($instance['label'], t('Field'));
      }
      foreach ($this->bundlesByType[$entity_type] as $bundle_name) {
        if (isset($keys[$bundle_name])) {
          $instance = field_info_instance($entity_type, $this->fieldKey, $bundle_name);
          $api->addRule($bundle_name, $keys[$bundle_name]);
          $api->descWithLabel($instance['label'], t('Field'), $bundle_name);
        }
      }
      // $api->addDescription('(' . $this->fieldKey . ')');
    }
  }
}