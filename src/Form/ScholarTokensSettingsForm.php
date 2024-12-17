<?php

declare(strict_types=1);

namespace Drupal\scholartokens\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure ScholarTokens settings for this site.
 */
final class ScholarTokensSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'scholartokens_scholar_tokens_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['scholartokens.settings'];
  }

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * Constructs a new CitationSelectSettingsForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $fields = $this->entityFieldManager->getFieldDefinitions('node', 'islandora_object');
    $date_fields = [];
    $range_fields = [];
    foreach ($fields as $field_name => $field_definition) {
      if ($field_definition->getType() === 'edtf') {
        $date_fields[$field_name] = $field_definition->getLabel();
      }
    }

    foreach ($fields as $field_name => $field_definition) {
      if ($field_definition->getType() === 'string') {
        $range_fields[$field_name] = $field_definition->getLabel();
      }
    }
    foreach ($fields as $field_name => $field_definition) {
      if ($field_definition->getType() === 'typed_relation') {
        $typed_relation_fields[$field_name] = $field_definition->getLabel();
      }
    }
    $form['field'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose date field to be tokenized'),
      '#options' => $date_fields,
      '#default_value' => $this->config('scholartokens.settings')->get('field'),
    ];

    $form['range_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose range field to be tokenized'),
      '#options' => $range_fields,
      '#default_value' => $this->config('scholartokens.settings')->get('range_field'),
    ];

    $form['contributor_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Choose contributor field to be tokenized'),
      '#options' => $typed_relation_fields,
      '#default_value' => $this->config('scholartokens.settings')->get('range_field'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if ($form_state->getValue('example') === 'wrong') {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('The value is not correct.'),
    //     );
    //   }
    // @endcode
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->config('scholartokens.settings')
      ->set('field', $form_state->getValue('field'))
      ->set('range_field', $form_state->getValue('range_field'))
      ->set('contributor_field', $form_state->getValue('contributor_field'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
