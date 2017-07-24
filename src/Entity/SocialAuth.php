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
 *     "provider_user_id" = "provider_user_id",
 *     "additional_data" = "additional_data"
 *   },
 * )
 */
class SocialAuth extends ContentEntityBase implements ContentEntityInterface {

  /**
   * Creating fields.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields['id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('ID'))
      ->setDescription(t('The ID of the Social Auth record.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The Social Auth user UUID.'))
      ->setReadOnly(TRUE);

    // The ID of user account associated.
    $fields['user_id'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('User ID'))
      ->setDescription(t('The Drupal uid associated with social network.'));

    // Name of the social network account associated.
    $fields['plugin_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plugin ID'))
      ->setDescription(t('Identifier for Social Auth implementer.'));

    // Unique Account ID returned by the social network provider.
    $fields['provider_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Provider user ID'))
      ->setDescription(t('The unique user ID in the provider.'));

    // Additional Data ccollected social network provider.
    $fields['additional_data'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Additional data'))
      ->setDescription(t('The additional data kept for future use.'));

    return $fields;
  }

}
