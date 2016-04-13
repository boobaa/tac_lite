<?php

/**
 * @file
 * Contains \Drupal\tac_lite\TACLiteSchemeListBuilder.
 */

namespace Drupal\tac_lite;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of TAC Lite Scheme entities.
 */
class TACLiteSchemeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('TAC Lite Scheme');
    $header['id'] = $this->t('Machine name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    // You probably want a few more properties here...
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build = parent::render();
    $build['message'] = [
      '#markup' => $this->t('Currently only view, edit, delete permissions possible, so 7 permutations will be more than enough.'),
    ];
    return $build;
  }

}
