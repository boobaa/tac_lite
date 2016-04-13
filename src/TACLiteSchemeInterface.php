<?php

/**
 * @file
 * Contains \Drupal\tac_lite\TACLiteSchemeInterface.
 */

namespace Drupal\tac_lite;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining TAC Lite Scheme entities.
 */
interface TACLiteSchemeInterface extends ConfigEntityInterface {
  // Add get/set methods for your configuration properties here.

  /**
   * Returns the Scheme label.
   *
   * @return string
   *   The label for this Scheme.
   */
  public function getLabel();

  /**
   * Sets the Scheme label.
   *
   * @param string $label
   *   The desired label.
   *
   * @return $this
   */
  public function setLabel($label);

  /**
   * Returns the Scheme permissions.
   *
   * @return string[]
   *   The permissions for this Scheme.
   */
  public function getPermissions();

  /**
   * Sets the Scheme permissions.
   *
   * @param string[] $permissions
   *   The desired permissions.
   *
   * @return $this
   */
  public function setPermissions($permissions);

  /**
   * Returns the Scheme term visibility.
   *
   * @return boolean
   *   The term visibility for this Scheme.
   */
  public function getTermVisibility();

  /**
   * Sets the Scheme term visibility.
   *
   * @param boolean $term_visibility
   *   The desired term visibility.
   *
   * @return $this
   */
  public function setTermVisibility($term_visibility);

  /**
   * Returns the Scheme grants.
   *
   * @return int[][][]
   *   The grants for this Scheme. Keys are:
   *   - first level: role ID,
   *   - second level: vocabulary ID,
   *   - third level: term ID.
   *   Innermost values are term IDs as well.
   */
  public function getGrants();

  /**
   * Sets the Scheme grants.
   *
   * @param int[][][] $grants
   *   The desired grants. Keys are:
   *   - first level: role ID,
   *   - second level: vocabulary ID,
   *   - third level: term ID.
   *   Innermost values are term IDs as well.
   *
   * @return $this
   */
  public function setGrants($grants);

}
