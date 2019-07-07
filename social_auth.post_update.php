<?php

/**
 * @file
 * Method hook_post_update_NAME.
 */

/**
 * Encrypts all tokens currently stored by Social Auth.
 */
function social_auth_post_update_encrypt_tokens(&$sandbox = NULL) {

  $ids = \Drupal::entityQuery('social_auth')->execute();
  $total = count($ids);

  // Initializes some variables during the first pass through.
  if (!isset($sandbox['total'])) {
    $sandbox['total'] = $total;
    $sandbox['progress'] = 0;
  }

  $nodes_per_batch = 25;

  // Handles one pass through.
  $ids = \Drupal::entityQuery('social_auth')
    ->range($sandbox['progress'], $sandbox['progress'] + $nodes_per_batch)
    ->execute();

  /** @var \Drupal\social_auth\Entity\SocialAuth[] $social_auth_users */
  $social_auth_users = \Drupal::entityTypeManager()
    ->getStorage('social_auth')
    ->loadMultiple($ids);

  foreach ($social_auth_users as $user) {
    $token = $user->get('token')->value;

    // Sets token take care of the encryption.
    $user->setToken($token)->save();

    $sandbox['progress']++;
  }

  if ($sandbox['total'] == 0) {
    $sandbox['#finished'] = 1;
  }
  else {
    $sandbox['#finished'] = ($sandbox['progress'] / $sandbox['total']);
  }

  // Once finished.
  if ($sandbox['#finished']) {
    $ids = \Drupal::entityQuery('social_auth')->execute();

    return t('Updated %n Social Auth users', [
      '%n' => count($ids),
    ]);
  }

}
