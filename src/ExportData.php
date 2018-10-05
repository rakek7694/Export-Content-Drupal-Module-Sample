<?php

namespace Drupal\demo_export_data;

use Drupal\node\Entity\Node;

/**
 * Class ExportData.
 *
 * @package Drupal\demo_export_data\Form
 */
class ExportData {

  /**
   * Function to export the content of node.
   */
  public static function exportDataDemo($node_type, &$context) {
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['current_node'] = 0;
      $context['sandbox']['max'] = db_query("SELECT COUNT(nid) FROM {node_field_data} where type='$node_type'")->fetchField();
    }
    $limit = 100;
    $query_nid = db_select('node_field_data', 'nd')->distinct();
    $query_nid->fields('nd', ['nid']);
    $query_nid->condition('nd.nid', $context['sandbox']['current_node'], '>');
    $query_nid->orderBy('nd.nid');
    $query_nid->condition('nd.type', $node_type, '=');
    $query_nid->range(0, $limit);
    $nids = $query_nid->execute()->fetchAll();
    if ($nids) {
      foreach ($nids as $key => $value) {
        $node = Node::load($value->nid);
        $results_final = [];
        $results_final['nid'] = $node->nid->value;
        $results_final['type'] = $node->getType();
        $results_final['title'] = $node->getTitle();
        $results_final['body'] = str_replace(["\r", "\n"], '', strip_tags($node->body->value));
        $context['results'][] = $results_final;
        $context['sandbox']['progress']++;
        $context['sandbox']['current_node'] = $node->nid->value;
        $context['message'] = t('Now processing %node', ['%node' => $node->nid->value]);
      }
    }
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

  /**
   * Function for finished callback.
   */
  public function exportDataFinishedCallback($success, $results_final, $operations, $node_type) {
    if ($success) {
      $message = \Drupal::translation()->formatPlural(count($results_final), 'One post processed.', '@count posts processed.');
    }
    else {
      $error_operation = reset($operations);
      $message = t('An error occurred while processing');
    }
    foreach ($results_final as $result) {
      $items[] = t('Loaded node %title.', ['%title' => $result]);
    }
    $filename = "ExportedNode.csv";
    $fp = fopen('php://output', 'w');
    // ('Content-Encoding: UTF-8');.
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    echo "\xEF\xBB\xBF";
    fputcsv($fp, array_keys($results_final[0]));
    foreach ($results_final as $fields) {
      fputcsv($fp, $fields);
    }
    fclose($fp);
    drupal_set_message($message);
    exit($message);
  }

}
