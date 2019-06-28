<?php


/**
 * Update encryption tokens in Social Auth.
 */
function social_auth_post_update_changes_to_db_true()  {

  for($i = 1; $i <= 100000; ++$i) {
      $arr[] = $i;
  }

  $entity = \Drupal::entityTypeManager()
    ->getStorage('social_auth')
    ->loadMultiple($arr);

  foreach ($entity as $user) {
    $token = $user->get('token')->value;
    $user->setToken($token);
    $user->save();
    $result = t('Token %nid saved', [
      '%nid' => $user
        ->id(),
    ]);
  }
}
