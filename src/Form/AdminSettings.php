<?php

/**
 * @file
 * Contains \Drupal\tac_lite\Form\AdminSettings.
 */

namespace Drupal\tac_lite\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\ProxyClass\Routing\RouteBuilder;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configures tac_lite settings for this site.
 */
class AdminSettings extends ConfigFormBase {

  /**
   * The vocabulary storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $vocabulary_storage;

  /**
   * The router builder service.
   *
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  protected $route_builder;

  /**
   * Constructs a \Drupal\tac_lite\AdminSettings object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage for vocabularies.
   * @param \Drupal\Core\ProxyClass\Routing\RouteBuilder $route_builder
   *   The route builder service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityStorageInterface $entity_storage, RouteBuilder $route_builder) {
    $this->vocabulary_storage = $entity_storage;
    $this->route_builder = $route_builder;
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')->getStorage('taxonomy_vocabulary'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tac_lite_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['tac_lite.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $vocabularies = taxonomy_vocabulary_get_names();

    if (!count($vocabularies)) {
      $form['body'] = array(
        '#markup' => t('You must <a href="!url">create a vocabulary</a> before you can use tac_lite.',
          array('!url' => Url::fromRoute('entity.taxonomy_vocabulary.add_form')->toString())),
      );
    }
    else {
      $config = $this->config('tac_lite.settings');

      $options = array();
      foreach ($vocabularies as $vocabulary) {
        $vocabulary = $this->vocabulary_storage->load($vocabulary);
        $options[$vocabulary->id()] = $vocabulary->label();
      }

      $form['categories'] = array(
        '#type' => 'select',
        '#title' => t('Vocabularies'),
        '#default_value' => $config->get('categories'),
        '#options' => $options,
        '#description' => t('Select one or more vocabularies to control privacy.  <br/>Use caution with hierarchical (nested) taxonomies as <em>visibility</em> settings may cause problems on node edit forms.<br/>Do not select free tagging vocabularies, they are not supported.'),
        '#multiple' => TRUE,
        '#required' => TRUE,
      );
      // TAC Lite Schemes are now config entities, so we don't provide a select
      // here to set the number of them.
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * This form submit callback ensures that the form values are saved, and also
   * the node access database table is rebuilt.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    // First, save settings the default way.
    $config = $this->config('tac_lite.settings');
    $config->set('categories', $form_state->getValue('categories'));
    $config->save();
    // Next, rebuild the node_access table.
    node_access_rebuild(TRUE);
  }

}
