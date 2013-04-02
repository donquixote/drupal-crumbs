<?php


function crumbs_debug_page() {
  drupal_set_title('Crumbs debug');
  $path = isset($_GET['path_to_test']) ? $_GET['path_to_test'] : '';
  $path_checked = check_plain($path);
  $form_action = url('admin/structure/crumbs/debug');

  $input_html = <<<EOT
<input id="path" type="text" class="form-text" size="40" name="path_to_test" value="$path_checked"/>
EOT;
  $input_html = t('Path: !text_field', array('!text_field' => $input_html));
  $text = <<<EOT
<p>This page allows to have a look "behind the scenes", so you can analyse
which crumbs plugins and rules are responsible for the "crumbs parent" to a
given system path.</p>
<p>Crumbs will "ask" different plugins, until one of them responds with a parent
path candidate. The candidate with the highest weight wins.</p>
<p>Crumbs will stop invoking any more plugins, once it is clear that further
candidates will only have smaller weights.</p>
EOT;
  $text = t($text);

  $html = <<<EOT
    <form method="get" action="$form_action">
      $text
      <label for="path">$input_html</label>
    </form>
EOT;
  if (strlen($path)) {
    crumbs_Util::benchmark();
    $log = array();

    $path = drupal_get_normal_path($path);

    $log[] = crumbs_Util::benchmark('drupal_get_normal_path()');

    $item = crumbs_get_router_item($path);

    $log[] = crumbs_Util::benchmark('crumbs_get_router_item("' . $path . '")');

    crumbs('pluginDiscovery')->getAvailablePlugins();

    $log[] = crumbs_Util::benchmark("crumbs('pluginDiscovery')->getAvailablePlugins()");

    $engine = crumbs('pluginEngine');

    $log[] = crumbs_Util::benchmark("crumbs('pluginEngine')");

    $title_candidates = array();
    $title_candidate_key = NULL;
    $title = $engine->findTitle($path, $item, array(), $title_candidates, $title_candidate_key);

    $log[] = crumbs_Util::benchmark('find title');

    $parent_path_candidates = array();
    $parent_path_candidate_key = NULL;
    $parent_path = $engine->findParent($path, $item, $parent_path_candidates, $parent_path_candidate_key);
    $parent_path = drupal_get_normal_path($parent_path);
    $parent_item = crumbs_get_router_item($parent_path);

    $log[] = crumbs_Util::benchmark('find parent');

    if (!empty($parent_item)) {

      $parent_title_candidates = array();
      $parent_title_candidate_key = NULL;
      $parent_title = $engine->findTitle($parent_path, $parent_item, array(), $parent_title_candidates, $parent_title_candidate_key);
    }

    $log[] = crumbs_Util::benchmark('find parent title');

    $log_html = implode('<br/>', $log);

    $default_parent = crumbs_reduce_path($path);
    $default_row = array(
      '',
      '(default)',
      $item['title'],
      $default_parent,
      drupal_get_normal_path($default_parent),
      empty($parent_item) ? '' : $parent_item['title'],
    );

    $sort = array();
    $rule_found = FALSE;
    foreach (array('title', 'parent_path', 'parent_path', 'parent_title') as $i => $k) {
      if (!isset(${$k . '_candidates'})) {
        continue;
      }
      if (!isset($$k)) {
        $default_row[$i + 2] = '<strong>' . $default_row[$i + 2] . '</strong>';
      }
      foreach (${$k . '_candidates'} as $key => $x) {
        list($weight, $candidate_raw, $candidate) = $x;
        if ($i === 1) {
          $candidate = $candidate_raw;
        }
        if (FALSE === $weight) {
          // This one is disabled. Continue.
          $weight = 'FALSE';
        }
        elseif (is_null($weight)) {
          $weight = '';
        }
        elseif (!is_numeric($weight)) {
          $weight = '?';
        }
        if (!isset($sort[$weight][$key])) {
          $sort[$weight][$key] = array(
            $weight,
            $key,
            '',
            '',
            '',
            '',
          );
        }
        if (FALSE === $candidate) {
          $candidate = 'FALSE';
        }
        elseif (is_null($candidate)) {
          $candidate = '-';
        }
        if ($key === ${$k . '_candidate_key'}) {
          $candidate = '<strong>' . check_plain($candidate) . '</strong>';
        }
        $sort[$weight][$key][$i + 2] = $candidate;
      }
    }

    ksort($sort);
    $all = array();
    foreach ($sort as $weight => $rows) {
      foreach ($rows as $key => $row) {
        $all[] = $row;
      }
    }

    // Default row.
    $all[] = $default_row;

    $head = array(
      'Weight',
      'Key',
      'Title candidate',
      'Parent path, raw',
      'Parent path, processed',
      'Parent title candidate',
    );
    $table = theme('table', array(
      'header' => $head,
      'rows' => $all,
    ));

    $html .= <<<EOT
      <p>$text</p>
      <p>$log_html</p>
      $table
EOT;
  }
  return $html;
}
