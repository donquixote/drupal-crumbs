<?php
use Drupal\crumbs\PluginSystem\Discovery\Collection\DescriptionCollectionInterface;

/**
 * Injected API object for the describe() method of mono plugins.
 */
class crumbs_InjectedAPI_describeMonoPlugin {

  private $pluginKey;

  /**
   * @var DescriptionCollectionInterface
   */
  private $descriptionCollection;

  /**
   * @param string $pluginKey
   * @param \Drupal\crumbs\PluginSystem\Discovery\Collection\DescriptionCollectionInterface $descriptionCollection
   */
  function __construct($pluginKey, DescriptionCollectionInterface $descriptionCollection) {
    $this->pluginKey = $pluginKey;
    $this->descriptionCollection = $descriptionCollection;
  }

  /**
   * @param string $title
   */
  function setTitle($title) {
    $this->descriptionCollection->addDescription($this->pluginKey, $title);
  }

  /**
   * @param string $title
   *   The untranslated title / description.
   * @param string[] $args
   *   Placeholder values to insert into the translated description.
   *
   * @see t()
   * @see format_string()
   */
  function translateTitle($title, $args = array()) {
    $this->descriptionCollection->translateDescription($this->pluginKey, $title, $args);
  }

  /**
   * @param string $title
   * @param string $label
   */
  function titleWithLabel($title, $label) {
    $this->translateTitle('@key: @value', array(
      '@key' => $label,
      '@value' => $title,
    ));
  }
}
