<?php

namespace Drupal\crumbs\Tests;

use Drupal\crumbs\PluginSystem\Discovery\Collection\EntityPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\LabeledPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Collection\RawPluginCollection;
use Drupal\crumbs\PluginSystem\Discovery\Hook\Arg\PluginCollectionArg;
use Drupal\crumbs\PluginSystem\Engine\FactoryUtil;
use Drupal\crumbs\PluginSystem\Engine\ParentFinderEngine;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs\Router\MockRouter;

class PluginEngineTest extends \PHPUnit_Framework_TestCase {

  function testRouteParentPluginEngine() {
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
    $this->assertEquals(
      array(
        'foo1.bar2' => 'foo1.bar2',
        'foo2.bar1' => 'foo2.bar1',
      ),
      $engine->findAllParents('xyz', array())
    );
  }

  function testPluginEngine() {
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
    $pluginEngine = FactoryUtil::createParentFinder($parentPluginCollection, $statusMap, $router);

    $this->assertEquals(
      array(
        'foo1.bar1' => 'foo1.bar1',
      ),
      $pluginEngine->findAllParents('xyz', array('route' => 'other/route'))
    );
    $this->assertEquals(
      array(
        'foo1.bar2' => 'foo1.bar2',
        'foo1.bar1' => 'foo1.bar1',
      ),
      $pluginEngine->findAllParents('xyz', array('route' => 'node/%'))
    );
  }

  function testHookCrumbsPlugins() {
    $parentPluginCollection = new LabeledPluginCollection();
    $titlePluginCollection = new LabeledPluginCollection();
    $entityParentPluginCollection = new EntityPluginCollection();
    $entityTitlePluginCollection = new EntityPluginCollection();
    $api = new PluginCollectionArg(
      $parentPluginCollection,
      $titlePluginCollection,
      $entityParentPluginCollection,
      $entityTitlePluginCollection);

    $api->setModule('menu');
    $api->describeFindParent('hierarchy.*', 'Menu hierarchy');
    $api->describeFindParent('hierarchy.main-menu', 'Main menu');

    $entityParentPluginCollection->finalize(
      $parentPluginCollection,
      TRUE);

    $entityTitlePluginCollection->finalize(
      $titlePluginCollection,
      FALSE);

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
