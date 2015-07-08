<?php

namespace Drupal\crumbs_ui\Controller;

use Drupal\crumbs\PageData;
use Drupal\crumbs_ui\Widget\BreadcrumbBuilderDemo;
use Drupal\crumbs_ui\Widget\MarkupWidget;
use Drupal\crumbs_ui\Widget\ParentFinderDemo;
use Drupal\crumbs_ui\Widget\ParentPluginDemo;
use Drupal\crumbs_ui\Widget\PathSelectorWidget;
use Drupal\crumbs_ui\Widget\RouterItemDemo;
use Drupal\crumbs_ui\Widget\TabsWidget;
use Drupal\crumbs_ui\Widget\TitleFinderDemo;
use Drupal\crumbs_ui\Widget\TitlePluginDemo;
use Drupal\crumbs_ui\Widget\TrailFinderDemo;

class DebugController implements ControllerInterface {

  /**
   * The main controller method.
   *
   * @return array
   */
  function handle() {

    // @todo Is this needed?
    drupal_set_title('Crumbs debug');

    $path_to_test = $this->getPathToTest();

    $build = array();

    $pathSelectorWidget = new PathSelectorWidget($path_to_test);
    $build['path_selector'] = $pathSelectorWidget->build();

    $tabs = new TabsWidget();

    $routerItem = crumbs()->router->getRouterItem($path_to_test);

    if (!isset($routerItem)) {
      $tabs->addChild(
        'router-item',
        new MarkupWidget(t('The path does not resolve to a router item.')),
        'Router item');
    }
    else {
      $tabs->addChild(
        'router-item',
        new RouterItemDemo($routerItem),
        t('Router item'));

      $tabs->addChild(
        'parent-finding-plugins',
        new ParentPluginDemo($routerItem),
        t('Parent-finding plugins'));

      $tabs->addChild(
        'parent-finding-decorators',
        new ParentFinderDemo($routerItem),
        t('Parent-finding decorators'));

      $tabs->addChild(
        'title-finding-plugins',
        new TitlePluginDemo($routerItem),
        t('Title-finding plugins'));

      $tabs->addChild(
        'title-finding-decorators',
        new TitleFinderDemo($routerItem),
        t('Title-finding decorators'));
    }

    $pageData = new PageData(
      crumbs()->trailFinder,
      crumbs()->breadcrumbBuilder,
      crumbs()->breadcrumbFormatter,
      crumbs()->router);

    $pageData->path = $path_to_test;

    $tabs->addChild(
      'trail',
      new TrailFinderDemo($path_to_test),
      t('Trail-finding'));

    $trail = crumbs()->trailFinder->buildTrail($path_to_test);

    $tabs->addChild(
      'breadcrumb-items',
      new BreadcrumbBuilderDemo($trail),
      t('Breadcrumb items'));


    $build['tabs'] = $tabs->build();

    return $build;
  }

  /**
   * @return string
   */
  protected function getPathToTest() {

    $path_to_test = '';
    if (isset($_GET['path_to_test'])) {
      $path_to_test = $_GET['path_to_test'];
    }
    elseif (!empty($_SESSION['crumbs.admin.debug.history'])) {
      foreach ($_SESSION['crumbs.admin.debug.history'] as $path => $true) {
        if ('admin' !== substr($path, 0, 5)) {
          $path_to_test = $path;
        }
        elseif ('admin/structure/crumbs' !== substr($path, 0, 22)) {
          $admin_path_to_test = $path;
        }
      }
      if (empty($path_to_test) && !empty($admin_path_to_test)) {
        $path_to_test = $admin_path_to_test;
      }
    }

    return $path_to_test;
  }
}
