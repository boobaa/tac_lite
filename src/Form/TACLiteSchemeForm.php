<?php

/**
 * @file
 * Contains \Drupal\tac_lite\Form\TACLiteSchemeForm.
 */

namespace Drupal\tac_lite\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tac_lite\Entity\TACLiteScheme;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TACLiteSchemeForm.
 *
 * @package Drupal\tac_lite\Form
 */
class TACLiteSchemeForm extends EntityForm {

  /**
   * The term storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $term_storage;

  /**
   * Constructs a \Drupal\tac_lite\Form\TACLiteSchemeForm object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $term_storage
   *   The entity storage for terms.
   */
  public function __construct(EntityStorageInterface $term_storage) {
    $this->term_storage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $vids = $this->config('tac_lite.settings')->get('categories');

    if (count($vids)) {
      $roles = user_roles();

      /** @var TACLiteScheme $tac_lite_scheme */
      $tac_lite_scheme = $this->entity;
      $form['label'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Scheme name'),
        '#maxlength' => 255,
        '#default_value' => $tac_lite_scheme->label(),
        '#description' => $this->t("A human-readable name for administrators to see. For example, 'read' or 'read and write'."),
        '#required' => TRUE,
      ];

      $form['id'] = [
        '#type' => 'machine_name',
        '#default_value' => $tac_lite_scheme->id(),
        '#machine_name' => [
          'exists' => '\Drupal\tac_lite\Entity\TACLiteScheme::load',
        ],
        '#disabled' => !$tac_lite_scheme->isNew(),
      ];
      // Currently, only view, update and delete are supported by node_access
      $options = [
        'grant_view' => 'view',
        'grant_update' => 'update',
        'grant_delete' => 'delete',
      ];

      $form['permissions'] = [
        '#type' => 'select',
        '#title' => $this->t('Permissions'),
        '#multiple' => TRUE,
        '#options' => $options,
        '#default_value' => $tac_lite_scheme->getPermissions(),
        '#description' => $this->t('Select which permissions are granted by this scheme.  <br/>Note when granting update, it is best to enable visibility on all terms.  Otherwise a user may unknowingly remove invisible terms while editing a node.'),
        '#required' => FALSE,
      ];

      $form['term_visibility'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Visibility'),
        '#description' => $this->t('If checked, this scheme determines whether a user can view <strong>terms</strong>.  Note the <em>view</em> permission in the select field above refers to <strong>node</strong> visibility.  This checkbox refers to <strong>term</strong> visibility, for example in a content edit form or tag cloud.'),
        '#default_value' => $tac_lite_scheme->getTermVisibility(),
      ];

      $form['helptext'] = [
        '#markup' => $this->t('To grant to an individual user, visit the <em>access by taxonomy</em> tab on the account edit page.'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];
      $form['helptext2'] = [
        '#markup' => $this->t('To grant by role, select the terms below.'),
        '#prefix' => '<p>',
        '#suffix' => '</p>',
      ];

      $all_defaults = $tac_lite_scheme->getGrants();
      $form['grants'] = ['#tree' => TRUE];
      foreach ($roles as $rid => $role) {
        $form['grants'][$rid] = [
          '#type' => 'fieldset',
          '#tree' => TRUE,
          '#title' => $this->t('Grant permission by role: %role', ['%role' => $role->label()]),
          '#description' => $this->t(''),
          '#collapsible' => TRUE,
        ];

        $defaults = isset($all_defaults[$rid]) ? $all_defaults[$rid] : NULL;
        foreach ($vids as $vid) {
          $tree = $this->term_storage->loadTree($vid);
          $options = ['<' . $this->t('none') . '>'];
          foreach ($tree as $term) {
            $options[$term->tid] = $term->name;
          }

          $form['grants'][$rid][$vid] = [
            '#type' => 'select',
            '#title' => $this->t('Parent terms'),
            '#options' => $options,
            '#default_value' => isset($defaults[$vid]) ? $defaults[$vid] : NULL,
            '#multiple' => TRUE,
          ];
        }
      }
    }
    else {
      return [
        'body' => [
          '#markup' => $this->t('First select vocabularies on the <a href=!url>settings page</a>.', [
            '!url' => Url::fromRoute('tac_lite.admin_settings')->toString()
          ])
        ]
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $tac_lite_scheme = $this->entity;
    $status = $tac_lite_scheme->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label TAC Lite Scheme.', [
          '%label' => $tac_lite_scheme->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label TAC Lite Scheme.', [
          '%label' => $tac_lite_scheme->label(),
        ]));
    }
    $form_state->setRedirectUrl($tac_lite_scheme->toUrl('collection'));
  }

}
