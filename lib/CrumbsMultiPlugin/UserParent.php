<?php

class crumbs_CrumbsMultiPlugin_UserParent extends crumbs_CrumbsMultiPlugin_EntityParentAbstract {

  protected $weights = array();

  function describe($api) {
    foreach (user_roles(TRUE) as $rid => $role) {
      $api->addRule($role);
    }
  }

  function initWeights($weight_keeper) {
    foreach (user_roles(TRUE) as $rid => $role) {
      $weight = $weight_keeper->findWeight($role);
      if (FALSE !== $weight) {
        $this->weights[$rid] = $weight;
      }
    }
    asort($this->weights);
  }

  /**
   * Find candidates for the parent path.
   *
   * @param string $path
   *   The path that we want to find a parent for.
   * @param array $item
   *   Item as returned from crumbs_get_router_item()
   *
   * @return array
   *   Parent path candidates
   */
  function findParent__user_x($path, $item) {
    $user = $item['map'][1];
    // Load the user if it hasn't been loaded due to a missing wildcard loader.
    $user = is_numeric($user) ? user_load($user) : $user;
    if (empty($user) || !is_object($user)) {
      return;
    }

    $candidates = array();
    foreach ($this->weights as $rid => $weight) {
      if (!empty($user->roles[$rid])) {
        $role = $user->roles[$rid];
        $parent = $this->plugin->entityFindParent($user, 'user', $role);
        if (!empty($parent)) {
          $candidates[$role] = $parent;
        }
      }
    }
    return $candidates;
  }
}
