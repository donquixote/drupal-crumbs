<?php

class crumbs_CrumbsEntityPlugin_TokenEnabled extends crumbs_CrumbsEntityPlugin_TokenDisabled {

  /**
   * @inheritdoc
   */
  function entityFindCandidate($entity, $entity_type, $distinction_key) {

    // This is cached..
    $patterns = variable_get('crumbs_' . $entity_type . '_parent_patterns', array());

    if (!empty($patterns[$distinction_key])) {

      // Use token to resolve the pattern.
      $info = entity_get_info($entity_type);
      $parent = token_replace($patterns[$distinction_key],
        array(
          $info['token type'] => $entity,
        ),
        array(
          'language' => $GLOBALS['language'],
          'callback' => 'crumbs_clean_token_values',
        )
      );

      if (!empty($parent)) {
        // Only accept candidates where all tokens are fully resolved.
        // This means we can't have literal '[' in the path - so be it.
        if (FALSE === strpos($parent, '[')) {
          return $parent;
        }
      }
    }
  }
}