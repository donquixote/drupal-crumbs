<?php

namespace Drupal\crumbs\PluginSystem\FieldTypePlugin;

interface FieldTypePluginInterface {

  /**
   * @param array[] $items
   *   Field items from the field instance.
   *
   * @return string
   *   Title or parent path candidate.
   */
  function fieldItemsFindCandidate(array $items);
}
