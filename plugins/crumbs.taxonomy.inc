<?php
// $Id$


function taxonomy_crumbs_plugins() {
  return array(
    'hierarchy' => new _taxonomy_CrumbsPlugin__hierarchy(),
    'term_node' => new _taxonomy_CrumbsPlugin__term_node(),
    'term_name' => new _taxonomy_CrumbsPlugin__term_name(),
    'admin'     => new _taxonomy_CrumbsPlugin__admin(),
  );
}


class _taxonomy_CrumbsPlugin {

  function define($h) {
    foreach (taxonomy_get_vocabularies() as $voc_id => $voc) {
      $h->addRule('voc_'. $voc_id, 'Vocabulary: '. $voc->name);
    }
  }
}


class _taxonomy_CrumbsPlugin__hierarchy extends _taxonomy_CrumbsPlugin {

  /**
   * Terms get their parent terms as breadcrumb parent.
   * The method name matches the router path "taxonomy/term/%".
   */
  function findParent__taxonomy_term__($path, $item) {
    $terms = $item['fragments'][2];
    $terms = taxonomy_terms_parse_string($terms);
    if (isset($terms['tids']) && count($terms['tids']) === 1) {
      $tid = $terms['tids'][0];
      $q = db_query($sql = "
        SELECT h.parent, d.vid
        FROM {term_hierarchy} h
        LEFT JOIN {term_data} d ON (h.tid = d.tid)
        WHERE d.tid = %d
        GROUP BY d.vid
      ", $tid);
      $result = array();
      while ($row = db_fetch_object($q)) {
        if ($row->parent) {
          $result['voc_'.$row->vid] = 'taxonomy/term/'.$row->parent;
        }
      }
      return $result;
    }
  }
}


class _taxonomy_CrumbsPlugin__term_node extends _taxonomy_CrumbsPlugin {

  /**
   * Nodes get their terms as breadcrumb parents.
   * The method name matches the router path "node/%".
   */
  function findParent__node__($path, $item) {
    $node = $item['map'][0];
    if (is_array($node->taxonomy)) {
      $result = array();
      foreach ($node->taxonomy as $tid => $term) {
        if (!isset($result['voc_'.$term->vid])) {
          $result['voc_'.$term->vid] = "taxonomy/term/$tid";
        }
      }
      return $result;
    }
  }
}


class _taxonomy_CrumbsPlugin__term_name {

  function findTitle__taxonomy_term__($path, $item) {
    $terms = $item['fragments'][2];
    $terms = taxonomy_terms_parse_string($terms);
    if (!isset($terms['tids']) || !count($terms['tids'])) {
      return;
    }
    return $this->_buildTermsTitle($terms['tids']);
  }

  /**
   * Terms get their parent terms as breadcrumb parent.
   * The method name matches the router path "taxonomy/term/%".
   */
  protected function _buildTermsTitle($tids) {
    $db_placeholders = db_placeholders($tids);
    $q = db_query($sql_r = db_rewrite_sql($sql = "
      SELECT t.tid, t.name
      FROM {term_data} t
      WHERE t.tid IN ($db_placeholders)
    ", 't', 'tid'), $tids);
    // we rebuild the $names-array so it only contains terms the user has access to.
    $names = array();
    while ($term = db_fetch_object($q)) {
      $names[$term->tid] = $term->name;
    }
    return implode(', ', $names);
  }
}


class _taxonomy_CrumbsPlugin__admin {

  function findTitle__admin_content_taxonomy__($path, $item) {
    $voc = $item['map'][1];
    return $voc->name;
  }
}




