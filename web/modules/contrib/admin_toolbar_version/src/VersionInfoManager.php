<?php

namespace Drupal\admin_toolbar_version;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class of version info manager.
 */
class VersionInfoManager {

  use StringTranslationTrait;
  /**
   * The request stack service.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The config entity for this manager.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * The module extension list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extension_list;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $file_system;

  /**s
   * VersionInfoManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list
   *   The module extension list service.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleExtensionList $extension_list, FileSystemInterface $file_system, RequestStack $request_stack) {
    $this->extension_list = $extension_list;
    $this->config = $config_factory->getEditable('admin_toolbar_version.settings');
    $this->file_system = $file_system;
    $this->requestStack = $request_stack;
  }

  /**
   * Get item url.
   *
   * @return string
   *   Return the url to fetch the version from.
   */
  public function getUrl() : string {
    $url = $this->config->get('version_url') ?? "/";
    if (empty($url)) {
      $url = "/";
    }
    $url = Url::fromUserInput($url)->toUriString();
    return $url;
  }

  /**
   * Get application version from source module or install profile.
   *
   * @return string
   *   Return the version string form the configured source module or profile.
   */
  public function getApplicationVersion() : string {
    $version = '';

    $version_source = $this->config->get('version_source');
    if (empty($version_source)) {
      $version_source = \Drupal::installProfile();
    }

    $info = $this->extension_list->getExtensionInfo($version_source);

    if (isset($info['version'])) {
      $version = $info['version'];
    }

    return $version;
  }

  /**
   * Get the current drupal version.
   *
   * @return string
   *   Return the current drupal version string.
   */
  public function getDrupalVersion() : string {
    return \Drupal::VERSION;
  }

  /**
   * Get the current GIT branch.
   *
   * @return string
   *   Return the current GIT branch name.
   */
  public function getGitBranch() : string {
    $branch = '';
    $environment = $this->getEnvironmentConfig();
    // Extract GIT information.
    if ($environment && $environment['git']) {
      $git = $environment['git'];
      $path = $this->file_system->realpath(DRUPAL_ROOT . $git);
      if (file_exists($path)) {
        $git_file = file_get_contents($path);
        $branch = trim(implode('/', array_slice(explode('/', $git_file ?: ''), 2)));
      }
    }
    return $branch;
  }

  /**
   * Get the environment name.
   *
   * @return string
   *   The current environment string.
   */
  public function getEnvironment() : string {

    $config = $this->getEnvironmentConfig();

    return $config['name'] ?? '';
  }

  /**
   * Assemble a menu title.
   *
   * @return string
   *   The generated version number title string.
   */
  public function getTitle() : string {

    $title = [
      'drupal' => $this->getDrupalVersion(),
      'version' => $this->getApplicationVersion(),
      'environment' => $this->getEnvironment(),
      'git' => $this->getGitBranch(),
    ];

    return implode(' - ', array_filter($title));
  }

  /**
   * Get custom styling.
   *
   * @return array
   *   An array containing the styling for the version title item.
   *   Contains a key vor the color and the name of the icon.
   */
  public function getStyle() : array {
    $style = [];
    $environment = $this->getEnvironment();
    if (!empty($environment)) {
      $config = $this->getEnvironmentConfig();
      $style = [
        'color' => $config['color'] ?? '#0000FF',
        'icon' => preg_replace('@[^a-z0-9_]+@', '_', trim(strtolower($environment))),
      ];
    }

    return $style;
  }

  /**
   * To get environment config.
   *
   * @return array
   *   An array containing the config settings per environment.
   */
  protected function getEnvironmentConfig() : array {
    static $environment = [];

    if (!$environment) {

      // Get environment.
      $request = $this->requestStack->getCurrentRequest();
      $environments = $this->config->get('environments');
      foreach ($environments as $econfig) {

        // Skip if domain isn't matched.
        if (!empty($econfig['domain']) && !preg_match($econfig['domain'], $request->getHost())) {
          continue;
        }

        // Skip if $_ENV isn't matched.
        if (!empty($econfig['variable']) && !isset($_ENV[$econfig['variable']])) {
          continue;
        }

        // Skip if neither domain or $_ENV variable is given.
        if (empty($econfig['domain']) && empty($econfig['variable'])) {
          continue;
        }

        $environment = $econfig;

        break;
      }
    }

    return $environment;
  }

}
