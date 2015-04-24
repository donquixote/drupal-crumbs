<?php

namespace Drupal\crumbs\Tests;

use Drupal\crumbs\ParentCollector\ParentCollector;
use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\ArgumentInterface;
use Drupal\crumbs\PluginSystem\Discovery\Hook\MockHookCrumbsPlugins;
use Drupal\crumbs\PluginSystem\Discovery\PluginDiscovery;
use Drupal\crumbs\PluginSystem\Engine\FactoryUtil;
use Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\Router\MockRouter;

class ParentCollectorTest extends \PHPUnit_Framework_TestCase {

  function testParentFinderEngine() {
    $pluginKeys = array(
      'foo1.bar1',
      'foo1.bar2',
      'foo2.bar1',
    );
    $plugins = array();
    foreach ($pluginKeys as $pluginKey) {
      $plugins[$pluginKey] = new \crumbs_MonoPlugin_FixedParentPath($pluginKey);
    }
    $statuses = array(
      '*' => TRUE,
      'foo1.bar1' => FALSE,
    );
    $weights = array(
      '*' => 0,
      'foo1.*' => 1,
    );
    $router = new MockRouter();
    $statusMap = PluginStatusWeightMap::create($statuses, $weights);
    $pluginsByWeight = FactoryUtil::groupParentPluginsByWeight($plugins, $statusMap);
    $pluginsSorted = FactoryUtil::flattenPluginsByWeight($pluginsByWeight);
    $engine = new ParentFinderEngine($pluginsSorted, $router);
    $parentCollector = new ParentCollector($engine);
    $this->assertEquals(
      array(
        'foo1.bar2' => 'foo1.bar2',
        'foo2.bar1' => 'foo2.bar1',
      ),
      $parentCollector->findAllParentRouterItems(array('link_path' => 'xyz')));
  }

  function testParentFinder() {
    $parentPluginCollection = new RawPluginCollection();
    $parentPluginCollection->addMonoPlugin('foo1.bar1', new \crumbs_MonoPlugin_FixedParentPath('foo1.bar1'));
    $parentPluginCollection->addMonoPlugin('foo1.bar2', new \crumbs_MonoPlugin_FixedParentPath('foo1.bar2'), 'node/%');
    $merged = array(
      '*' => 0,
      'foo1.bar1' => 2,
      'foo1.*' => 1,
    );
    $statusMap = new PluginStatusWeightMap($merged);
    $router = new MockRouter();
    $parentCollector = ParentCollector::create($parentPluginCollection, $statusMap, $router);

    $this->assertEquals(
      array(
        'foo1.bar1' => 'foo1.bar1',
      ),
      $parentCollector->findAllParentRouterItems(array(
        'link_path' => 'other/route',
        'route' => 'other/route',
      ))
    );
    $this->assertEquals(
      array(
        'foo1.bar2' => 'foo1.bar2',
        'foo1.bar1' => 'foo1.bar1',
      ),
      $parentCollector->findAllParentRouterItems(array(
        'link_path' => 'node/123',
        'route' => 'node/%',
      ))
    );
  }

  function testHookCrumbsPlugins() {
    $parentPluginCollection = new LabeledPluginCollection();
    $titlePluginCollection = new LabeledPluginCollection();

    $discovery = new PluginDiscovery(
      new MockHookCrumbsPlugins(
        function(ArgumentInterface $api) {
          $api->setModule('menu');
          $api->describeFindParent('hierarchy.*', 'Menu hierarchy');
          $api->describeFindParent('hierarchy.main-menu', 'Main menu');
        }));

    $discovery->discoverPlugins($parentPluginCollection, $titlePluginCollection);

    $this->assertEquals(
      array(
        'menu-hierarchy.*' => 'Menu hierarchy',
        'menu-hierarchy.main-menu' => 'Main menu',
      ),
      $parentPluginCollection->getDescriptions()
    );

    $this->assertEquals(
      array(),
      $parentPluginCollection->getRoutelessPlugins()
    );

    $this->assertEquals(
      array(),
      $parentPluginCollection->getRoutePluginsByRoute()
    );

    $this->assertEquals(
      array(),
      $parentPluginCollection->getDefaultStatuses()
    );
  }

}
