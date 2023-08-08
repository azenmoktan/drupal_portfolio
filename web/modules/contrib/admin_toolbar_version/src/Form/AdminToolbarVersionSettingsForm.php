<?php

namespace Drupal\admin_toolbar_version\Form;

use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * This Form use for Admin Toolbar Version Settings .
 */
class AdminToolbarVersionSettingsForm extends ConfigFormBase {

  /**
   * The UUID service.
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidService;

  /**
   * The ModuleExtensionList service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The InfoParser service.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;

  /**
   * The menu link manager service.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Constructs a new AdminToolbarVersionSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_service
   *   The UUID service.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The InfoParser service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The ModuleExtensionList service.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, UuidInterface $uuid_service, InfoParserInterface $info_parser, ModuleExtensionList $module_extension_list, MenuLinkManagerInterface $menu_link_manager) {
    parent::__construct($config_factory);
    $this->infoParser = $info_parser;
    $this->moduleExtensionList = $module_extension_list;
    $this->uuidService = $uuid_service;
    $this->menuLinkManager = $menu_link_manager;
  }

  /**
   * Creates an instance of this class.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container to resolve services.
   *
   * @return static
   *   The instance of this class.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('info_parser'),
      $container->get('extension.list.module'),
      $container->get('uuid'),
      $container->get('plugin.manager.menu.link'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'admin_toolbar_version';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'admin_toolbar_version.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('admin_toolbar_version.settings');

    $form['#title'] = $this->t('Admin Toolbar Version');
    $form['#tree'] = TRUE;

    foreach ($config->get('environments') as $id => $environment) {
      $form['environments'][$id] = [
        '#type' => 'details',
        '#title' => $environment['name'],
        '#open' => FALSE,
        'name' => [
          '#type' => 'textfield',
          '#title' => 'Name',
          '#description' => $this->t('The name that should be displayed in the toolbar'),
          '#default_value' => $environment['name'],
        ],
        'domain' => [
          '#type' => 'textfield',
          '#title' => 'Domain',
          '#description' => $this->t('Enter a preg_match pattern to match the host (eg. "/www\.domain\.com/" ).'),
          '#default_value' => $environment['domain'],
        ],
        'variable' => [
          '#type' => 'textfield',
          '#title' => 'Variable',
          '#description' => $this->t('Enter the value as available in $_ENV'),
          '#default_value' => $environment['variable'],
        ],
        'color' => [
          '#type' => 'textfield',
          '#title' => 'Color',
          '#description' => $this->t('Enter the css color for the background of the toolbar item (eg. #FF0000 or red)'),
          '#default_value' => $environment['color'],
        ],
        'git' => [
          '#type' => 'textfield',
          '#title' => 'Git',
          '#description' => $this->t('Path to the GIT HEAD file (relative to Drupal root), Leave empty to not show GIT info.'),
          '#default_value' => $environment['git'],
        ],
      ];
    }

    $form['environments'][0] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => 'Add new environment',
      'name' => [
        '#type' => 'textfield',
        '#title' => 'Name',
        '#description' => $this->t('The name that should be displayed in the toolbar. Leave empty to remove an environment.'),
        '#default_value' => '',
      ],
      'domain' => [
        '#type' => 'textfield',
        '#title' => 'Domain',
        '#description' => $this->t('Enter a preg_match pattern to match the host (eg. "/www\.domain\.com/" ).'),
        '#default_value' => '',
      ],
      'variable' => [
        '#type' => 'textfield',
        '#title' => 'Variable',
        '#description' => $this->t('Enter the value as available in $_ENV'),
        '#default_value' => '',
      ],
      'color' => [
        '#type' => 'textfield',
        '#title' => 'Color',
        '#description' => $this->t('Enter the css color for the background of the toolbar item (eg. #FF0000 or red)'),
        '#default_value' => '',
      ],
      'git' => [
        '#type' => 'textfield',
        '#title' => 'Git',
        '#description' => $this->t('Path to the GIT HEAD file (relative to Drupal root), Leave empty to not show GIT info.'),
        '#default_value' => '/.git/HEAD',
      ],
    ];

    /** @var \Drupal\Core\Extension\ExtensionList $list */

    $list = $this->moduleExtensionList->getList();
    $list_options = [];
    foreach ($list as $name => $item) {
      $list_options[$name] = $item->getName();
    }

    $form['version_source'] = [
      '#type' => 'select',
      '#options' => $list_options,
      '#title' => 'Version source',
      '#description' => $this->t('The module to grab the version information from.'),
      '#default_value' => $config->get('version_source') ?? $this->infoParser->parse('profile', 'install'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get values.
    $environments = $form_state->getValue('environments');

    // Make sure the new environment gets a uuid.
    if (isset($environments[0])) {

      $uuid = $this->uuidService->generate();
      $environments[$uuid] = $environments[0];
      unset($environments[0]);
    }

    // Remove empty environments.
    $environments = array_filter($environments, function ($environment) {
      return !empty($environment['name']);
    });

    // Save environments.
    $config = $this->config('admin_toolbar_version.settings');
    $config->set('environments', $environments);

    // Save version source.
    $config->set('version_source', $form_state->getValue('version_source', ''));

    $config->save();

    // Clear cache so admin menu can rebuild.
    $this->menuLinkManager->rebuild();
    parent::submitForm($form, $form_state);
  }

}
