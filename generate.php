<?php

$exceptions = array(
  'src/ParentFinder',
  'src/PluginSystem/Engine',
  'src/PluginSystem/Wrapper',
  'lib/MonoPlugin/FixedParentPath.php',
  'modules/ui/src/Widget/ParentFinderDemo.php',
  'modules/ui/src/Widget/ParentPluginDemo.php',
  'modules/ui/src/Form/EntityParentForm.php',
);

$exceptions_map = array();
foreach ($exceptions as $exception) {
  $exceptions_map[__DIR__ . '/' . $exception] = TRUE;
}

generate(__DIR__, $exceptions_map);

function generate($dir_0, array $exceptions) {
  static $replace = array(
    '/* TITLE ONLY * /' => '/* TITLE ONLY */',
    '/* PARENT ONLY */' => '/* PARENT ONLY * /',
    'parent::' => 'parent::',
    'parentPath' => 'title',
    'ParentPath' => 'Title',
    'parent_path' => 'title',
    'parent path' => 'title',
    'Parent path' => 'Title',
    'parent' => 'title',
    'Parent' => 'Title',
  );
  $dir_1 = strtr($dir_0, $replace);
  if (!is_dir($dir_1)) {
    mkdir($dir_1);
  }
  foreach (scandir($dir_0) as $x_0) {
    if ('.' === $x_0{0}) {
      continue;
    }
    $x_1 = strtr($x_0, $replace);

    $path_0 = $dir_0 . '/' . $x_0;
    if (!empty($exceptions[$path_0])) {
      continue;
    }

    $path_1 = $dir_1 . '/' . $x_1;
    if (is_dir($path_0)) {
      generate($path_0, $exceptions);
    }
    elseif ($path_0 === $path_1) {
      // Nothing.
    }
    elseif (is_file($path_0)) {
      $extension = pathinfo($path_0, PATHINFO_EXTENSION);
      if (in_array($extension, array('php', 'txt', 'md', 'inc'))) {
        $contents = file_get_contents($path_0);
        $contents = strtr($contents, $replace);
        file_put_contents($path_1, $contents);
      }
    }
  }
}
