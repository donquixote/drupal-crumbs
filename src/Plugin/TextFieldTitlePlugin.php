<?php

namespace Drupal\crumbs\Plugin;

use Drupal\crumbs\PluginSystem\FieldTypePlugin\FieldTypePluginInterface;

class TextFieldTitlePlugin implements FieldTypePluginInterface {

  /**
   * @param array[] $items
   *   Field items from the field instance.
   *
   * @return string
   *   Title or parent path candidate.
   */
  function fieldItemsFindCandidate(array $items) {
    foreach ($items as $item) {
      return $item['value'];
    }

    return NULL;
  }
}
