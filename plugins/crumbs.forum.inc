<?php
// $Id$


class forum_class_CrumbsParentFinder {
  function construct() {
    return array(
      'hierarchy' => new _forum_CrumbsParentFinder__hierarchy,
      'term_node' => new _forum_CrumbsParentFinder__term_node,
      'term_name' => new _forum_CrumbsParentFinder__term_name,
    );
  }
}


class _forum_CrumbsParentFinder__hierarchy {
  
  /**
   * Terms get their parent terms as breadcrumb parent.
   * The method name matches the router path "taxonomy/term/%".
   */
  function find__forum($path, $item) {
    if (preg_match('/^forum\/(\d+)$/', $path, $m)) {
      list(,$tid) = $m;
      $q = db_query($sql = "SELECT parent FROM {term_hierarchy} WHERE tid = %d", $tid);
      while ($row = db_fetch_object($q)) {
        if ($row->parent) {
          return 'forum/'.$row->parent;
        }
      }
    }
  }
}


class _forum_CrumbsParentFinder__term_node {
  
  /**
   * Forum nodes get their forum terms as breadcrumb parents.
   * The method name matches the router path "node/%".
   */
  function find__node__($path, $item) {
    $node = $item->getArg(0);
    if ($node->type == 'forum' && is_array($node->taxonomy) && $vid = $this->_getForumVid()) {
      $result = array();
      foreach ($node->taxonomy as $tid => $term) {
        if ($term->vid == $vid) {
          return 'forum/'.$tid;
        }
      }
    }
  }
  
  function _getForumVid() {
    return variable_get('forum_nav_vocabulary', NULL);
  }
}


class _forum_CrumbsParentFinder__term_name {
  
  function decorate__forum($path, $item) {
    if (preg_match('/^forum\/(\d+)$/', $path, $m)) {
      list(,$tid) = $m;
      $q = db_query($sql = "SELECT name FROM {term_data} WHERE tid = %d", $tid);
      if ($row = db_fetch_object($q)) {
        $item->title = $row->name;
      }
    }
  }
}







