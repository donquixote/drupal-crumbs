<?php


function crumbs_debug_page() {// return 'TEST';
  drupal_set_title('Crumbs debug');
  $path = $_GET['path_to_test'];
  $path_checked = check_plain($path);
  $form_action = url('admin/build/crumbs/debug');
  $html = <<<EOT
    <form method="get" action="$form_action">
      <p>Path:
      <input size="40" name="path_to_test" value="$path_checked"/></p>
    </form>
EOT;
  if (isset($path)) {
    module_load_include('inc', 'crumbs', 'crumbs.plugin_engine');
    module_load_include('inc', 'crumbs', 'crumbs.trail');
    
    crumbs_benchmark();
    $log = array();
    
    $path = drupal_get_normal_path($path);
    
    $log[] = crumbs_benchmark('drupal_get_normal_path()');
    
    $item = crumbs_get_router_item($path);
    
    $log[] = crumbs_benchmark('crumbs_get_router_item("' . $path . '")');
    
    $parent_finder = crumbs_get_parent_finder();
    
    $log[] = crumbs_benchmark('crumbs_get_parent_finder()');
    
    $parent_path = $parent_finder->getParentPath($path, $item);
    
    $log[] = crumbs_benchmark('$finder->getParentPath()');
    $log_html = implode('<br/>', $log);
    
    $log = $parent_finder->getLoggedCandidates($path);
    $rows = array();
    $sort = array();
    foreach ($log as $key => $x) {
      list($candidate, $weight) = $x;
      $key_html = $key;
      if ($key === $item['crumbs_candidate_key']) {
        $key_html = '<strong>'. $key_html .'</strong>';
        $candidate = '<strong>'. $candidate .'</strong>';
      }
      if ($weight === FALSE) {
        $key_html = '<strike>'. $key_html .'</strike>';
        $candidate = '<strike>'. $candidate .'</strike>';
        $weight = 'disabled';
      }
      else if (is_null($weight)) {
        $candidate = '-';
        $key_html = '<i>'. $key_html .'</i>';
        $weight = '-';
      }
      $rows[$key] = <<<EOT
        <tr><td>$weight</td><td>$key_html</td><td>$candidate</td></tr>
EOT;
      $sort[$key] = $weight;
    }
    // array_multisort($sort, $rows);
    $rows_html = implode('', $rows);
    $parent_path_checked = check_plain($parent_path);
    $html .= <<<EOT
      <p>Parent found: <strong>$parent_path_checked</strong></p>
      <p>$log_html</p>
      <table>
        <thead><tr><th>Weight</th><th>Key</th><th>Parent path candidate</th></tr></thead>
        <tbody>$rows_html</tbody>
      </table>
EOT;
  }
  return $html;
}



