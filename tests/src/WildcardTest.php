<?php

namespace Drupal\crumbs\Tests;

use Drupal\crumbs_ui\PluginKey\Util;

class WildcardTest extends \PHPUnit_Framework_TestCase {

  function testFindParent() {
    $this->assertNull(Util::pluginKeyGetParent('*'));
    $this->assertEquals('*', Util::pluginKeyGetParent('menu.*'));
    $this->assertEquals('menu.*', Util::pluginKeyGetParent('menu.hierarchy.*'));
    $this->assertEquals('menu.hierarchy.*', Util::pluginKeyGetParent('menu.hierarchy.main-menu'));
  }
}
