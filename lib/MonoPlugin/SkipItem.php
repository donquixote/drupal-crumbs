<?php

class crumbs_MonoPlugin_SkipItem implements crumbs_MonoPlugin_FindTitleInterface {

  /**
   * {@inheritdoc}
   */
  function findTitle($path, $item) {
    return FALSE;
  }
}
