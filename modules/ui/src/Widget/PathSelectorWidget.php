<?php

namespace Drupal\crumbs_ui\Widget;

class PathSelectorWidget implements WidgetInterface {

  /**
   * @var string
   */
  protected $pathToTest;

  /**
   * @param string $pathToTest
   */
  function __construct($pathToTest) {
    $this->pathToTest = $pathToTest;
  }

  /**
   * @return array|string
   *   A render array, or a html snippet.
   */
  function build() {


    $path_checked = check_plain($this->pathToTest);
    $form_action = url('admin/structure/crumbs/debug');

    $input_html = <<<EOT
<input id="crumbs-admin-debug-path" type="text" class="form-text" size="40" name="path_to_test" value="$path_checked" />
<input type="submit" value="Go" class="form-submit" />
EOT;
    $input_html = t('Path to test') . ': ' . $input_html;

    $html = <<<EOT
      <form method="get" action="$form_action">
        <label for="path">$input_html</label>
      </form>
EOT;

    return array('#markup' => $html);
  }
}
