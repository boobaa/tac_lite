<?php

/**
 * @file
 * Contains \Drupal\tac_lite\Entity\TACLiteScheme.
 */

namespace Drupal\tac_lite\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\tac_lite\TACLiteSchemeInterface;

/**
 * Defines the TAC Lite Scheme entity.
 *
 * @ConfigEntityType(
 *   id = "tac_lite_scheme",
 *   label = @Translation("TAC Lite Scheme"),
 *   handlers = {
 *     "list_builder" = "Drupal\tac_lite\TACLiteSchemeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\tac_lite\Form\TACLiteSchemeForm",
 *       "edit" = "Drupal\tac_lite\Form\TACLiteSchemeForm",
 *       "delete" = "Drupal\tac_lite\Form\TACLiteSchemeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\tac_lite\TACLiteSchemeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "tac_lite_scheme",
 *   admin_permission = "administer tac_lite",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/people/access/tac_lite/schemes/{tac_lite_scheme}",
 *     "add-form" = "/admin/people/access/tac_lite/schemes/add",
 *     "edit-form" = "/admin/people/access/tac_lite/schemes/{tac_lite_scheme}/edit",
 *     "delete-form" = "/admin/people/access/tac_lite/schemes/{tac_lite_scheme}/delete",
 *     "collection" = "/admin/people/access/tac_lite/schemes"
 *   }
 * )
 */
class TACLiteScheme extends ConfigEntityBase implements TACLiteSchemeInterface {
  /**
   * The TAC Lite Scheme ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The TAC Lite Scheme label.
   *
   * @var string
   */
  protected $label;

  /**
   * The TAC Lite Scheme permissions.
   *
   * @var string[]
   */
  protected $permissions;
//  $permissions['grant_view'] = 'grant_view';

  /**
   * The TAC Lite Scheme term visibility.
   *
   * @var boolean
   */
  protected $term_visibility;

  /**
   * The TAC Lite Scheme grants.
   *
   * @var integer[][][]
   */
  protected $grants;
//  $grants[$rid][$vid][$tid] = $tid;

  /**
   * @inheritdoc
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * @inheritdoc
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getPermissions() {
    return $this->permissions;
  }

  /**
   * @inheritdoc
   */
  public function setPermissions($permissions) {
    $this->permissions = $permissions;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getTermVisibility() {
    return $this->term_visibility;
  }

  /**
   * @inheritdoc
   */
  public function setTermVisibility($term_visibility) {
    $this->term_visibility = $term_visibility;
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getGrants() {
    return $this->grants;
  }

  /**
   * @inheritdoc
   */
  public function setGrants($grants) {
    $this->grants = $grants;
    return $this;
  }

}
