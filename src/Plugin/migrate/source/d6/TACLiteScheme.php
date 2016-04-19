<?php

namespace Drupal\tac_lite\Plugin\migrate\source\d6;

use Drupal\migrate\Row;
use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal 6 vocabularies source from database.
 *
 * @MigrateSource(
 *   id = "d6_tac_lite_scheme",
 *   source_provider = "tac_lite"
 * )
 */
class TACLiteScheme extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('variable', 'v')
      ->fields('v', ['name', 'value'])
      ->condition('name', db_like('tac_lite_config_scheme_') . '%', 'LIKE');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return array(
      'name' => $this->t('The D6 variable name of the config part of the TAC Lite scheme.'),
      'value' => $this->t('The D6 variable value of the config part of the TAC Lite scheme.'),
      'id' => $this->t('The TAC Lite scheme ID.'),
      'label' => $this->t('The name of the TAC Lite scheme.'),
      'permissions' => $this->t('The permissions for the TAC Lite scheme.'),
      'visibility' => $this->t('The term visibility for the TAC Lite scheme.'),
      'grants' => $this->t('The grants for the TAC Lite scheme.'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $scheme_id = substr($row->getSourceProperty('name'), strlen('tac_lite_config_scheme_'));
    $scheme_grants = $this->select('variable', 'v')
      ->fields('v', ['value'])
      ->condition('name', 'tac_lite_grants_scheme_' . $scheme_id)
      ->execute()
      ->fetchField();
    $scheme_config = unserialize($row->getSourceProperty('value'));

    $row->setSourceProperty('id', $scheme_config['name']);
    $row->setSourceProperty('label', $scheme_config['name']);
    $row->setSourceProperty('permissions', $scheme_config['perms']);
    // FIXME: Where is this stored in D6?
    $row->setSourceProperty('visibility', 1);

    // Flatten the source to an array of "$rid\t$vid\t$tid" items.
    $grants = [];
    foreach (unserialize($scheme_grants) as $rid => $role) {
      foreach ($role as $vid => $vocabulary) {
        foreach ($vocabulary as $tid) {
          $grants[] = "$rid\t$vid\t$tid";
        }
      }
    }

    $row->setSourceProperty('grants', $grants);
    return parent::prepareRow($row);
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
