<?php

$directories = array(
  'src',
  'lib',
);

$exceptions = array(
  'src/ParentFinder',
  'src/PluginSystem/Engine',
  'src/PluginSystem/Wrapper',
  'lib/MonoPlugin/FixedParentPath.php',
  # 'src/PluginApi/SpecFamily/Title/DefaultImplementation/Route.php',
  # 'lib/MonoPlugin/ParentPathCallback.php',
);

foreach ($directories as $directory) {
  if (!is_dir($directory)) {
    continue;
  }
  generate($directory, array_fill_keys($exceptions, TRUE));
}

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
    if ('.' === $x_0 || '..' === $x_0) {
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
      if ('php' === $extension) {
        $contents = file_get_contents($path_0);
        $contents = strtr($contents, $replace);
        file_put_contents($path_1, $contents);
      }
    }
  }
}
