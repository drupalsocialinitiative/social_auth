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
 *     "created" = "created",
 *     "changed" = "changed",
 *     "token" = "token",
 *     "additional_data" = "additional_data"
 *   },
 * )
 */
class SocialAuth extends ContentEntityBase implements ContentEntityInterface {

  /**
   * Set the Acesss token.
   *
   * @return \Drupal\social_auth\Entity
   *   Drupal Social Auth Entity.
   */
  public function setToken($token) {
    $this->set('token', $token);
    return $this;
  }

  /**
   * Returns the access token.
   *
   * @return string
   *   The user access token.
   */
  public function getToken() {
    return $this->get('token')->value;
  }

  /**
   * Set the Additional Data.
   *
   * @return \Drupal\social_auth\Entity
   *   Drupal Social Auth Entity.
   */
  public function setAdditionalData($data) {
    $this->set('additional_data', $data);
    return $this;
  }

  /**
   * Returns the Additional Data.
   *
   * @return string
   *   The user additional data.
   */
  public function getAdditionalData() {
    return $this->get('additional_data')->value;
  }

  /**
   * Updates the created time field.
   *
   * @return \Drupal\social_auth\Entity
   *   Drupal Social Auth Entity.
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * Gets the User creation time.
   *
   * @return int
   *   Creation timestamp Social Auth entity.
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * Updates the user data changed time field.
   *
   * @return \Drupal\social_auth\Entity
   *   Drupal Social Auth Entity.
   */
  public function setChangedTime($timestamp) {
    $this->set('changed', $timestamp);
    return $this;
  }

  /**
   * Gets the changed time field.
   *
   * @return int
   *   Changed timestamp Social Auth entity.
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
  }

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
    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The Drupal uid associated with social network.'))
      ->setSetting('target_type', 'user');

    // Name of the social network account associated.
    $fields['plugin_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plugin ID'))
      ->setDescription(t('Identifier for Social Auth implementer.'));

    // Unique Account ID returned by the social network provider.
    $fields['provider_user_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Provider user ID'))
      ->setDescription(t('The unique user ID in the provider.'));

    // User creation time.
    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    // User modified time.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    // Token received after user authentication.
    $fields['token'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Token received after user authentication'))
      ->setDescription(t('Used to make API calls.'));

    // Additional Data collected social network provider.
    $fields['additional_data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Additional data'))
      ->setDescription(t('The additional data kept for future use.'));

    return $fields;
  }

}
