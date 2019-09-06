<?php
 
/**
 * @file
 * Contains \Drupal\cm_show\Plugin\migrate\source\Show.
 */
 
namespace Drupal\cm_show\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
 
/**
 * Drupal 7 Blog node source plugin
 *
 * @MigrateSource(
 *   id = "custom_show"
 * )
 */
class Show extends SqlBase {
 
  /**
   * {@inheritdoc}
   */
  public function query() {
    // this queries the built-in metadata, but not the description, tags, or images.
    $query = $this->select('node', 'n')
      ->condition('n.type', 'cm_show')
      ->fields('n', array(
        'nid',
        'vid',
        'type',
        'language',
        'title',
        'uid',
        'status',
        'created',
        'changed',
        'promote',
        'sticky',
      ));
    $query->orderBy('nid');
    return $query;
  }
 
  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['description_format'] = $this->t('Format of description');
    $fields['description_value'] = $this->t('Full text of description');
    $fields['description_summary'] = $this->t('Summary of description');
    $fields['production_date'] = $this->t('Production Date');
    return $fields;
  }
 
  /**
   * {@inheritdoc}
   */

  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
 
    // description (compound field with value, summary, and format)
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_description_value,
        fld.field_description_summary,
        fld.field_description_format
      FROM
        {field_data_field_description} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      $row->setSourceProperty('description_value', $record->field_description_value );
      $row->setSourceProperty('description_summary', $record->field_description_summary );
      $row->setSourceProperty('description_format', $record->field_description_format );
    }


    $result = $this->getDatabase()->query('
      SELECT
        fld.field_show_production_date_value
      FROM
        {field_data_field_show_production_date} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      $row->setSourceProperty('production_date', $record->field_show_production_date_value );
    }

 
    // taxonomy term IDs
    // (here we use MySQL's GROUP_CONCAT() function to merge all values into one row.)
    $result = $this->getDatabase()->query('
      SELECT
        GROUP_CONCAT(fld.field_tags_tid) as tids
      FROM
        {field_data_field_tags} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      if (!is_null($record->tids)) {
        $row->setSourceProperty('show_tags', explode(',', $record->tids) );
      }
    }
 
    return parent::prepareRow($row);
  }
 
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';
    return $ids;
  }
 
}
