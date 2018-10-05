<?php

namespace Drupal\demo_export_data\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class DeleteNodeForm.
 *
 * @package Drupal\demo_export_data\Form
 */
class ExportDataForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'custom_data_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['select_node'] = [
      '#type' => 'select',
      '#title' => t('Select'),
      '#required' => TRUE,
      '#options' => node_type_get_names(),
    ];
    $form['custom_data_form'] = [
      '#type' => 'submit',
      '#value' => $this->t('Export'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $node_type = $form_state->getValue('select_node');

    $batch = [
      'title' => t('Exporting Nodes...'),
      'operations' => [
            [
              '\Drupal\demo_export_data\ExportData::exportDataDemo',
                [$node_type],
            ],
      ],
      'finished' => '\Drupal\demo_export_data\ExportData::exportDataFinishedCallback',
      'init_message' => t('Hold on we are preparing data for you ...'),
      'progress_message' => t('Processed @current out of @total.'),
    ];

    batch_set($batch);
  }

}
