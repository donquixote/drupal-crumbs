<?php

namespace Drupal\crumbs\Tests;

use Drupal\crumbs\PluginSystem\Tree\TreeUtil;

class TreeUtilTest extends \PHPUnit_Framework_TestCase {

  function testSpliceCandidates() {
    $candidates = array(
      'aaa.bbb' => 5,
    );
    $expected = array(
      'aaa' => array(
        'bbb' => 5,
      ),
    );
    $this->assertEquals(
      $expected,
      TreeUtil::spliceCandidates($candidates));
  }
}
