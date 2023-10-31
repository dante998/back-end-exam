<?php

/**
 * @file
 * Hooks provided by the Inline Entity Form module.
 */

/**
 * Perform alterations before an entity form is included in the IEF widget.
 *
 * @param array $entity_form
 *   Nested array of form elements that comprise the entity form.
 * @param FormStateInterface $form_state
 *   The form state of the parent form.
 */
function hook_inline_entity_form_entity_form_alter(array &$entity_form, FormStateInterface &$form_state) {
  if ($entity_form['#entity_type'] == 'commerce_line_item') {
    $entity_form['quantity']['#description'] = t('New quantity description.');
  }
}

/**
 * Perform alterations before the reference form is included in the IEF widget.
 *
 * The reference form is used to add existing entities through an autocomplete
 * field.
 *
 * @param array $reference_form
 *   Nested array of form elements that comprise the reference form.
 * @param FormStateInterface $form_state
 *   The form state of the parent form.
 */
function hook_inline_entity_form_reference_form_alter(array &$reference_form, FormStateInterface &$form_state) {
  $reference_form['entity_id']['#description'] = t('New autocomplete description');
}

/**
 * Alter the fields used to represent an entity in the IEF table.
 *
 * @param array $fields
 *   The fields, keyed by field name.
 * @param array $context
 *   An array with the following keys:
 *   - parent_entity_type: The type of the parent entity.
 *   - parent_bundle: The bundle of the parent entity.
 *   - field_name: The name of the reference field on which IEF is operating.
 *   - entity_type: The type of the referenced entities.
 *   - allowed_bundles: Bundles allowed on the reference field.
 *
 * @see \Drupal\inline_entity_form\InlineFormInterface::getTableFields()
 */
function hook_inline_entity_form_table_fields_alter(array &$fields, array $context) {
  if ($context['entity_type'] == 'commerce_product_variation') {
    $fields['field_category'] = [
      'type' => 'field',
      'label' => t('Category'),
      'weight' => 101,
    ];
  }
}

/**
 * React on a IEF widget entity being saved.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state of the parent form.
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *   The entity being inserted or updated.
 * @param bool $is_new
 *   Shows if we create (true) or update an entity (false).
 */
function hook_inline_entity_form_entity_save(FormStateInterface &$form_state, ContentEntityInterface $entity, $is_new) {
  $operation = ($is_new == TRUE) ? 'created' : 'updated';
  $message = 'Entity ' . $entity->id() . ' was ' . $operation . ' in parent entity ' . $form_state->getFormObject()->getEntity()->id();
  \Drupal::logger('mymodule')->notice($message);
}

/**
 * React on a IEF widget entity being deleted.
 *
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 *   The form state of the parent form.
 * @param \Drupal\Core\Entity\ContentEntityInterface $entity
 *   The entity being deleted.
 */
function hook_inline_entity_form_entity_delete(FormStateInterface &$form_state, ContentEntityInterface $entity) {
  $message = 'Entity ' . $entity->id() . ' was deleted in parent entity ' . $form_state->getformObject()->getEntity()->id();
  \Drupal::logger('mymodule')->notice($message);
}

/**
 * Alter the list of entity bundles, which can be created with IEF widgets.
 *
 * @param array $create_bundles
 *   A list of entity bundles.
 * @param array $context
 *   An array with the following keys:
 *   - parent_entity_type: The type of the parent entity.
 *   - parent_bundle: The bundle of the parent entity.
 *   - field_name: The name of the reference field on which IEF is operating.
 *   - entity_type: The type of the referenced entities.
 *   - allowed_bundles: Bundles allowed on the reference field.
 *
 * @see \Drupal\inline_entity_form\InlineFormInterface::getTableFields()
 */
function hook_inline_entity_form_create_bundles_alter(array &$create_bundles, array $context) {
  // Remove a the possibility to create cost items.
  if ($context['parent_entity_type'] == 'budget') {
    if (($key = array_search('budget_cost_item', $create_bundles)) !== FALSE) {
      unset($create_bundles[$key]);
    }
  }
  // Add a revenue item to the list of bundles to create.
  $create_bundles[] = 'budget_revenue_item';
}
