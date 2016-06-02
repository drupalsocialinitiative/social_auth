<?php
/**
 * @file
 * Contains \Drupal\social_auth\Plugin\Block\SocialAuthLoginBlock
 */
namespace Drupal\social_auth\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Social Auth Block for Login
 *
 * @Block(
 *   id = "social_auth_login",
 *   admin_label = @Translation("Social Auth Login"),
 * )
 */
class SocialAuthLoginBlock extends BlockBase implements ContainerFactoryPluginInterface {

	/**
	 * @var \Drupal\Core\Config\ImmutableConfig
	 */
	private $socialAuthConfig;

	/**
	 * SocialAuthLoginBlock constructor.
	 *
	 * @param array $configuration
	 * @param string $plugin_id
	 * @param mixed $plugin_definition
	 * @param \Drupal\Core\Config\ImmutableConfig $social_auth_config
	 */
	public function __construct(array $configuration, $plugin_id, $plugin_definition, ImmutableConfig $social_auth_config) {
		parent::__construct($configuration, $plugin_id, $plugin_definition);

		$this->socialAuthConfig = $social_auth_config;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
		return new static(
			$configuration,
			$plugin_id,
			$plugin_definition,
			$container->get('config.factory')->get('social_api.settings')
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function build() {
		return array(
			'#theme' => 'login_with',
			'#social_networks' => $this->socialAuthConfig->get('auth'),
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function blockAccess(AccountInterface $account) {
		if($account->isAnonymous()) {
			return AccessResult::allowed();
		}
		return AccessResult::forbidden();
	}
}
