<?php

/**
 * Interface for entity parent plugins registered with hook_crumbs_plugins().
 */
interface crumbs_EntityParentPlugin {

  /**
   * @param object $entity
   *   The entity on this path.
   * @param string $entity_type
   *   The entity type
   * @param string $distinction_key
   *   Typically the bundle name.
   *   On user entities, this is one of the roles of the user.
   *   (this might be called more than once per user)
   *
   * @return string
   *   A candidate for the parent path.
   */
  function entityFindParent($entity, $entity_type, $distinction_key);
}
