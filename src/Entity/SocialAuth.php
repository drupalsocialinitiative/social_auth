<?php

namespace Drupal\social_auth\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Defines the Social Auth entity.
 *
 * @ingroup social_auth
 *
 * @ContentEntityType(
 *   id = "social_auth",
 *   label = @Translation("SocialAuth"),
 *   base_table = "social_auth",
 *   entity_keys = {
 *     "id" = "id",
 *     "user_id" = "user_id",
 *     "plugin_id" = "plugin_id",
 *     "provider_user_id" = "provider_user_id"
 *   },
 * )
 */
class SocialAuth extends ContentEntityBase implements ContentEntityInterface {

  /**
   * Creating fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    // Standard field, used as UNIQUE ID for social media account associations.
    $fields['id'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Social Auth Record.'))
      ->setReadOnly(TRUE);

    // The ID of user account associated.
    $fields['user_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('user_id'))
      ->setDescription(t('The ID Of User Account Associated With Social Network.'))
      ->setReadOnly(TRUE);

    // Name of the social network account associated.
    $fields['plugin_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('PLUGIN ID'))
      ->setDescription(t('Identifier for social provider account.'))
      ->setReadOnly(TRUE);

    // Unique Account ID returned by the social network provider.
    $fields['provider_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('PROVIDER USER ID'))
      ->setDescription(t('The Unique ID Provided by Social Network.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

}
