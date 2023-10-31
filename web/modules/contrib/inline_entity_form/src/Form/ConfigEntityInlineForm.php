<?php

namespace Drupal\inline_entity_form\Form;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;

/**
 * Generic entity inline form handler.
 */
class ConfigEntityInlineForm extends EntityInlineForm {

  /**
   * {@inheritdoc}
   */
  public function getTableFields($bundles) {

    $fields = [];
    $fields['label'] = [
      'type' => 'label',
      'label' => $this->getEntityType()->getLabel(),
      'weight' => 1,
    ];

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function entityForm(array $entity_form, FormStateInterface $form_state) {
    /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
    $entity = $entity_form['#entity'];
    $form_object = $this->getFormObject($entity, $entity_form['#form_mode']);
    $entity_form += $form_object->form($entity_form, $this->getSubformState($entity_form, $form_state));
    $entity_form['#ief_element_submit'][] = [
      get_class($this),
      'submitCleanFormState',
    ];
    $this->expandCallbacks($form_object, $entity_form['#after_build']);
    $this->expandCallbacks($form_object, $entity_form['#pre_render']);
    // Allow other modules to alter the form.
    $this->moduleHandler->alter('inline_entity_form_entity_form', $entity_form, $form_state);

    return $entity_form;
  }

  /**
   * {@inheritdoc}
   */
  public function entityFormValidate(array &$entity_form, FormStateInterface $form_state) {
    // Perform entity validation only if the inline form was submitted,
    // skipping other requests such as file uploads.
    $triggering_element = $form_state->getTriggeringElement();
    if (!empty($triggering_element['#ief_submit_trigger'])) {
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
      $entity = $entity_form['#entity'];
      $this->buildEntity($entity_form, $entity, $form_state);

      $form_object = $this->getFormObject($entity, $entity_form['#form_mode']);
      $form_object->validateForm($entity_form, $this->getSubformState($entity_form, $form_state));

      foreach ($form_state->getErrors() as $message) {
        // $name may be unknown in $form_state and
        // $form_state->setErrorByName($name, $message) may suppress the error
        // message.
        $form_state->setError($triggering_element, $message);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function buildEntity(array $entity_form, EntityInterface $entity, FormStateInterface $form_state) {
    // Start section based on protected EntityForm::copyFormValuesToEntity()
    // method.
    $subform_state = $this->getSubformState($entity_form, $form_state);
    $values = $subform_state->getValues();
    if ($entity instanceof EntityWithPluginCollectionInterface) {
      // Do not manually update values represented by plugin collections.
      $values = array_diff_key($values, $entity->getPluginCollections());
    }
    // Invoke all specified builders for copying form values to entity fields.
    if (isset($entity_form['#entity_builders'])) {
      foreach ($entity_form['#entity_builders'] as $function) {
        call_user_func_array($function, [
          $entity->getEntityTypeId(),
          $entity,
          &$entity_form,
          &$form_state,
        ]);
      }
    }
    foreach ($values as $key => $value) {
      $entity->set($key, $value);
    }
    // End section based on protected EntityForm::copyFormValuesToEntity()
    // method.
  }

  /**
   * {@inheritdoc}
   *
   * Not applicable for config entities since they are not fieldable.
   */
  public static function submitCleanFormState(&$entity_form, FormStateInterface $form_state) {
  }

  /**
   * Get the form object for the config entity and form mode.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   The config entity.
   * @param string $form_mode
   *   The form mode.
   *
   * @return \Drupal\Core\Entity\EntityFormInterface
   *   The form object.
   */
  protected function getFormObject(ConfigEntityInterface $entity, $form_mode) {
    $form_object = $this->entityTypeManager->getFormObject($entity->getEntityTypeId(), $form_mode);
    $form_object->setEntity($entity);

    return $form_object;
  }

  /**
   * Get the subform state for the entity form.
   *
   * @param array $entity_form
   *   The entity form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state of the main form.
   *
   * @return \Drupal\Core\Form\SubformState
   *   The subform state for the entity form.
   */
  protected function getSubformState(array $entity_form, FormStateInterface $form_state) {
    return SubformState::createForSubform($entity_form, $form_state->getCompleteForm(), $form_state);
  }

  /**
   * Expand callbacks in the form ::methodName() to className::methodName().
   *
   * @param \Drupal\Core\Form\FormInterface $form_object
   *   The form object the callback belongs to.
   * @param array &$callbacks
   *   An array of callbacks to expand.
   */
  protected function expandCallbacks(FormInterface $form_object, array &$callbacks) {
    foreach ($callbacks as &$callback) {
      if (is_string($callback) && strpos($callback, '::') === 0) {
        $callback = [$form_object, substr($callback, 2)];
      }
    }
  }

}
