<?php

namespace Drupal\select_registration_roles\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a ConfigForm for select_registration_roles module.
 */
class SelectRegistrationConfigForm extends ConfigFormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId(): string {
    return 'select_registration_roles_form';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $config = $this->config('select_registration_roles.settings');
    $roles = select_registration_roles_all_roles(TRUE);
    $authRole = $form_state->getValue('select_registration_roles_auth_role');

    if (is_null($authRole) && !$config->get('select_registration_roles_auth_role') || $authRole === 0) {
      unset($roles[AccountInterface::AUTHENTICATED_ROLE]);
    }

    $form['select_registration_roles_auth_role'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Display the authenticated user role'),
      '#description' => $this->t("If checked, the authenticated user role will be displayed."),
      '#default_value' => $config->get('select_registration_roles_auth_role'),
      '#ajax' => [
        'callback' => [$this, 'authRoleCallback'],
        'event' => 'change',
        'wrapper' => 'user-registration-role-group-container',
      ],
    ];

    $form['roles_container'] = [
      '#type' => 'details',
      '#title' => $this->t('Roles settings'),
      '#prefix' => '<div id="user-registration-role-group-container">',
      '#suffix' => '</div>',
      '#open' => TRUE,
    ];

    $form['roles_container']['select_registration_roles_setby_admin'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Roles'),
      '#options' => $roles,
      '#description' => $this->t("Select roles that will be displayed on registration form."),
      '#default_value' => $config->get('select_registration_roles_setby_admin') ?? [],
    ];

    $form['roles_container']['select_registration_roles_admin_approval'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Approval Roles'),
      '#options' => $roles,
      '#description' => $this->t("Select roles that need admin approval."),
      '#default_value' => $config->get('select_registration_roles_admin_approval') ?? [],
    ];

    $form['select_registration_roles_required'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Required field'),
      '#description' => $this->t("This will make role field required on registration form."),
      '#default_value' => $config->get('select_registration_roles_required'),
    ];

    $form['select_registration_roles_multiple'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Allow select multiple roles'),
      '#description' => $this->t("This will make role field multivalued."),
      '#default_value' => $config->get('select_registration_roles_multiple'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Ajax callback.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return mixed
   *   Rendered array.
   */
  public function authRoleCallback(array &$form, FormStateInterface $form_state) {
    return $form['roles_container'];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('select_registration_roles.settings');

    $config->set('select_registration_roles_setby_admin', $form_state->getValue('select_registration_roles_setby_admin'));
    $config->set('select_registration_roles_admin_approval', $form_state->getValue('select_registration_roles_admin_approval'));
    $config->set('select_registration_roles_auth_role', $form_state->getValue('select_registration_roles_auth_role'));
    $config->set('select_registration_roles_required', $form_state->getValue('select_registration_roles_required'));
    $config->set('select_registration_roles_multiple', $form_state->getValue('select_registration_roles_multiple'));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets the configuration names that will be editable.
   *
   * @return array
   *   An array of configuration object names that are editable if called in
   *   conjunction with the trait's config() method.
   */
  protected function getEditableConfigNames(): array {
    return ['select_registration_roles.settings'];
  }

}
