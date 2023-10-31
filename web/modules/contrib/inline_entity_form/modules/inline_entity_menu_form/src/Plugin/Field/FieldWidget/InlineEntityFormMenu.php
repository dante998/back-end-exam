<?php

namespace Drupal\inline_entity_menu_form\Plugin\Field\FieldWidget;


use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\inline_entity_form\Plugin\Field\FieldWidget\InlineEntityFormComplex;

/**
 * Menu entity inline widget.
 *
 * @FieldWidget(
 *   id = "inline_entity_form_menu",
 *   label = @Translation("Inline entity form - Menu"),
 *   field_types = {
 *     "entity_reference",
 *     "entity_reference_revisions",
 *   },
 *   multiple_values = true
 * )
 */
class InlineEntityFormMenu extends InlineEntityFormComplex {
  public static function defaultSettings() {
    $settings =  parent::defaultSettings();
    $settings['form_mode'] = 'edit';
    return $settings;
  }


  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['form_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Form mode'),
      '#default_value' => 'edit',
      '#options' => ['edit' => 'Edit'],
      '#required' => TRUE,
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  protected function getInlineEntityForm($operation, $bundle, $langcode, $delta, array $parents, EntityInterface $entity = NULL) {
    $element = parent::getInlineEntityForm($operation, $bundle, $langcode, $delta, $parents, $entity);
    $element['#save_entity'] = TRUE;
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    return $field_definition->getFieldStorageDefinition()->getSetting('target_type') === 'menu';
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $entities = $form_state->get([
      'inline_entity_form',
      $this->getIefId(),
      'entities',
    ]);

    foreach ($entities as $key => $entity) {
      $row = &$element['entities'][$key];
      $title = $this->t('Edit Links');
      $row['actions']['ief_entity_menu'] = [
        '#type' => 'link',
        '#title' => $title,
        '#url' => Url::fromRoute('inline_entity_menu_form.menu.edit_links_form', ['menu' => $entity['entity']->id()]),
        '#attributes' => [
          'class' => ['use-ajax', 'btn-primary'],
          'data-dialog-type' => 'modal',
          'data-dialog-options' => Json::encode([
            'width' => 700,
            'closeOnEscape' => TRUE,
            'title' => $title,
            'autoResize' => TRUE,
          ]),
        ],
      ];
    }

    return $element;
  }
}
