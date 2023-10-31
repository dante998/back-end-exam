<?php

namespace Drupal\inline_entity_menu_form;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\menu_ui\MenuForm;

/**
 * Base form for menu edit forms.
 *
 * @internal
 */
class MenuFormLB extends MenuForm {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'menu.links';
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $menuForm =  parent::form($form, $form_state);
    $menuForm['label']['#access'] = FALSE;
    $menuForm['id']['#access'] = FALSE;
    $menuForm['description']['#access'] = FALSE;
    $menuForm['langcode']['#access'] = FALSE;
    $menuForm['simple_sitemap']['#access'] = FALSE;

    $title = $this->t('Add new link');
    $menuForm['addLink'] = [
      '#type' => 'link',
      '#weight' => 0,
      '#title' => $title,
      '#url' => Url::fromRoute('entity.menu.add_link_form', ['menu' => $this->entity->id()]),
      '#attributes' => [
        'class' => ['use-ajax', 'button'],
        'data-dialog-type' => 'dialog',
        'data-dialog-options' => Json::encode([
          'width' => 700,
          'title' => $title,
          'target' => 'drupal-dialog',
          'modal' => TRUE,
          'autoResize' => TRUE,
        ]),
      ],
    ];

    foreach ($menuForm['links']['links'] as $key => $links) {
      if (str_starts_with($key, '#')) {
        if ($key === '#empty') {
          unset($menuForm['links']['links'][$key]);
        }
        continue;
      }

      foreach ($links['operations']['#links'] as &$operation) {
        $operation['url']->mergeOptions([
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'dialog',
            'data-dialog-options' => Json::encode([
              'width' => 700,
              'modal' => TRUE,
              'target' => 'drupal-dialog',
              'autoResize' => TRUE,
            ])
          ]
        ]);
      }
    }
    return $menuForm;
  }

  /**
   * {@inheritdoc}
   */
  protected function actionsElement(array $form, FormStateInterface $form_state) {
    $actions = parent::actionsElement($form, $form_state);
    $actions['submit']['#submit'][] = '::close_dialog_ajax_submit';
    $actions['submit']['#attributes']['class'][] = 'use-ajax-submit';
    $actions['delete']['#access'] = FALSE;
    return $actions;
  }


  /**
   * Callback for close the Modal.
   */
  function close_dialog_ajax_submit(array $form, FormStateInterface &$form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new CloseModalDialogCommand());
    $form_state->setResponse($response);
  }
}
