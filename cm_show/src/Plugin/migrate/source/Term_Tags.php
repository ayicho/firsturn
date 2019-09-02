<?php
/**
 * @file
 * Contains \Drupal\cm_show\Plugin\migrate\source\Term_Tags.
 */

namespace Drupal\cm_show\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * Taxonomy: Tags.
 *
 * @MigrateSource(
 *   id = "my_migration_tags"
 * )
 */
class Term_Tags extends SqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('taxonomy_term_data', 'td');
    $query->join('taxonomy_index', 'ti', 'ti.tid = td.tid');
    $query->join('taxonomy_vocabulary', 'tv', 'tv.vid = td.vid');
    $query->join('node', 'n', 'n.nid = ti.nid');
    $query->fields('td', ['tid', 'name', 'description', 'weight'])
      ->distinct()
      ->condition('n.type', 'blog')
      ->condition('tv.machine_name', 'tags');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name'        => $this->t('Category name'),
      'description' => $this->t('Description'),
      'weight'      => $this->t('Weight'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'tid' => [
        'type'  => 'integer',
        'alias' => 'td',
      ],
    ];
  }

}
