<?php

/**
 * Update encryption tokens in Social Auth.
 */
function social_auth_post_update_add_encryption_to_access_tokens(&$sandbox = NULL) {
	$entity = \Drupal::entityTypeManager()
	  ->getStorage('social_auth');

	foreach($entity as $user) {
		$token = $user->get('token');
		$user->setToken($token);
		$user->save();
		$result = t('Token %nid saved', [
    	'%nid' => $user
    	  ->id(),
  		]);
	}

}
