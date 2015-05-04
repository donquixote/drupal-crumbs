<?php

namespace Drupal\crumbs_ui\Widget;


use Drupal\crumbs\Router\RouterInterface;
use Drupal\crumbs_ui\PluginKey\RawHierarchy;

class PluginResultTable implements WidgetInterface {

  /**
   * @var array[]
   */
  private $collected;

  /**
   * @var array[][]
   *   Format: $[$key][] = array($parentPath, $index)
   */
  private $resultsGrouped = array();

  /**
   * @var string[]
   */
  private $descriptions;

  /**
   * @param array[] $collected
   * @param string[] $descriptions
   * @param \Drupal\crumbs\Router\RouterInterface $router
   */
  function __construct(array $collected, array $descriptions, RouterInterface $router) {
    $foundBest = FALSE;
    $leafs = array();
    foreach ($collected as $index => $result) {
      list($parentPath, $key) = $result;
      $leafs[$key] = TRUE;
      $result = array($parentPath, $index);
      $parentRouterItem = $router->getRouterItem($parentPath);
      if (!isset($parentRouterItem)) {
        $result[] = t('Denied, no router item.');
      }
      elseif (empty($parentRouterItem['access'])) {
        $result[] = t('Denied, no access.');
      }
      elseif ($foundBest) {
        $result[] = t('Accepted, but too late.');
      }
      else {
        $result[] = t('Accepted.');
        $foundBest = TRUE;
      }
      $this->resultsGrouped[$key] = $result;
    }

    $this->hierarchy = RawHierarchy::createFromKeys($leafs);
    $this->collected = $collected;
    $this->descriptions = $descriptions;
  }

  /**
   * @return array
   *   A render array.
   */
  function build() {
    // TODO: Implement build() method.
    return array(
      '#theme' => 'table',
      '#rows' => $this->buildTableRows('*', 0),
      '#header' => array(
        t('Plugin key'),
        t('Candidate'),
        t('Index'),
        t('Accepted?'),
      ),
    );
  }

  /**
   * @param string $key
   * @param int $depth
   *
   * @return array
   */
  private function buildTableRows($key, $depth) {
    $rows = array();
    $rows[$key] = $this->buildParentRow($key, $depth);
    foreach ($this->hierarchy->keyGetChildren($key) as $childKey) {
      if ($this->hierarchy->keyIsWildcard($childKey)) {
        $rows += $this->buildTableRows($childKey, $depth + 1);
      }
      else {
        $rows[$childKey] = $this->buildLeafRow($childKey, $depth + 1);
      }
    }
    return $rows;
  }

  /**
   * @param string $key
   * @param int $depth
   *
   * @return array
   */
  private function buildParentRow($key, $depth) {
    return array(
      $this->indent($depth) . $this->keyGetDescription($key),
      '',
      '',
      '',
    );
  }

  /**
   * @param string $key
   * @param int $depth
   *
   * @return array
   */
  private function buildLeafRow($key, $depth) {
    $row = array();
    $row[] = $this->indent($depth) . $this->keyGetDescription($key);

    list($parentPath, $index, $accepted) = $this->resultsGrouped[$key];
    $row[] = $parentPath;
    $row[] = $index;
    $row[] = $accepted;

    return $row;
  }

  /**
   * @param int $depth
   *
   * @return mixed
   */
  private function indent($depth) {
    $indent = '';
    for ($i = 0; $i < $depth; ++$i) {
      $indent .= '&nbsp; &nbsp; &nbsp; ';
    }
    return $indent;
  }

  /**
   * @param string $key
   *
   * @return string
   */
  private function keyGetDescription($key) {
    return isset($this->descriptions[$key])
      ? $this->descriptions[$key]
      : $key;
  }
}
