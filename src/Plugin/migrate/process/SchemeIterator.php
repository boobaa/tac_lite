<?php

namespace Drupal\tac_lite\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * This plugin iterates and processes a TAC Lite Scheme's grants array.
 *
 * @see https://www.drupal.org/node/2135345
 *
 * @MigrateProcessPlugin(
 *   id = "tac_lite_scheme_iterator",
 *   handle_multiples = TRUE
 * )
 */
class SchemeIterator extends ProcessPluginBase {

  /**
   * Runs a process pipeline on each destination property per list item.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $return = [];
    foreach ($value as $item) {
      list ($rid, $vid, $tid) = explode("\t", $item);
      $new_row = new Row([
        'role' => $rid,
        'vocabulary' => $vid,
        'term' => $tid,
      ], []);
      if (array_key_exists('role', $this->configuration)) {
        $rid = $this->transformProperty('role', $rid, $migrate_executable, $new_row);
      }
      if (array_key_exists('vocabulary', $this->configuration)) {
        $vid = $this->transformProperty('vocabulary', $vid, $migrate_executable, $new_row);
      }
      if (array_key_exists('term', $this->configuration)) {
        $tid = $this->transformProperty('term', $tid, $migrate_executable, $new_row);
      }
      $return[$rid][$vid][$tid] = $tid;
    }
    return $return;
  }

  /**
   * Runs the process pipeline for a property of the current key.
   *
   * @param string
   *   The property.
   * @param string|int $key
   *   The current key.
   * @param \Drupal\migrate\MigrateExecutableInterface $migrate_executable
   *   The migrate executable helper class.
   * @param \Drupal\migrate\Row $row
   *   The current row after processing.
   *
   * @return mixed
   *   The transformed key.
   */
  protected function transformProperty($property, $key, MigrateExecutableInterface $migrate_executable, Row $row) {
    $process = array($property => $this->configuration[$property]);
    $migrate_executable->processRow($row, $process, $key);
    return $row->getDestinationProperty($property);
  }

  /**
   * {@inheritdoc}
   */
  public function multiple() {
    return TRUE;
  }

}
