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
 *     "type" = "type",
 *     "social_media_id" = "social_media_id"
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
    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('TYPE'))
      ->setDescription(t('Social Network Name.'))
      ->setReadOnly(TRUE);

    // Unique Account ID returned by the social network provider.
    $fields['social_media_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('SOCIAL MEDIA ID'))
      ->setDescription(t('The Unique ID Provided by Social Network.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

}
