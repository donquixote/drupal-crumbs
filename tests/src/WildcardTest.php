<?php

namespace Drupal\crumbs\Tests;

use Drupal\crumbs\PluginSystem\Settings\KeyMap;
use Drupal\crumbs\PluginSystem\Settings\PluginStatusWeightMap;
use Drupal\crumbs_ui\PluginKey\Util;

class WildcardTest extends \PHPUnit_Framework_TestCase {

  function testKeyMap() {
    $keys = array(
      '*' => true,
      'menu.*' => true,
    );
    $keyMap = new KeyMap($keys);
    $this->assertEquals('*', $keyMap->keyLookup('*'));
    $this->assertEquals('*', $keyMap->keyLookup('test.*'));
    $this->assertEquals('menu.*', $keyMap->keyLookup('menu.*'));
    $this->assertEquals('menu.*', $keyMap->keyLookup('menu.foo.*'));
    $this->assertEquals('menu.*', $keyMap->keyLookup('menu.foo'));
    $this->assertEquals('*', $keyMap->keyLookup('menu'));
  }

  function testWeightMap() {
    $weights = array(
      '*' => 0,
      'menu.*' => 5,
    );
    $statuses = array(
      '*' => TRUE,
      'menu.*' => FALSE,
      'menu.hierarchy.*' => TRUE,
    );
    $map = PluginStatusWeightMap::create($statuses, $weights);
    $this->assertTrue(0 === $map->keyGetWeightOrFalse('*'));
    $this->assertTrue(FALSE === $map->keyGetWeightOrFalse('menu.*'));
    $this->assertTrue(5 === $map->keyGetWeightOrFalse('menu.hierarchy.*'));
    $this->assertTrue(5 === $map->keyGetWeightOrFalse('menu.hierarchy.main-menu'));
  }

  function testFindParent() {
    $this->assertNull(Util::pluginKeyGetParent('*'));
    $this->assertEquals('*', Util::pluginKeyGetParent('menu.*'));
    $this->assertEquals('menu.*', Util::pluginKeyGetParent('menu.hierarchy.*'));
    $this->assertEquals('menu.hierarchy.*', Util::pluginKeyGetParent('menu.hierarchy.main-menu'));
  }
}
