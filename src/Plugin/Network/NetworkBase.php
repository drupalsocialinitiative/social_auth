<?php

namespace Drupal\social_auth\Plugin\Network;

use Drupal\social_api\Plugin\NetworkBase as SocialApiNetworkBase;

/**
 * Defines a Network Plugin for Social Auth.
 */
abstract class NetworkBase extends SocialApiNetworkBase implements NetworkInterface {}
