<?php

namespace Drupal\demo_mail\Form;

use Drupal\Component\Utility\EmailValidator;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configuration form for admin emails.
 */
class CustomEmailsConfigForm extends ConfigFormBase {

  /**
   * @var \Drupal\Component\Utility\EmailValidator $emailValidator
   */
   protected EmailValidator $emailValidator;

  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    // Inject the EmailValidator service into the form.
    $instance->emailValidator = $container->get('email.validator');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
   protected function getEditableConfigNames() {
    // Specify the configuration name that this form manages.
    return ['custom_emails_config.settings'];
  }

  /**
   * {@inheritdoc}
   */
   public function getFormId() {
    // Define a unique form ID for this configuration form.
    return 'custom_emails_config_form';
  }

  /**
   * {@inheritdoc}
   */
   public function buildForm(array $form, FormStateInterface $form_state) {
    // Load the current configuration settings.
    $config = $this->config('custom_emails_config.settings');
    // Retrieve the existing admin email addresses or set an empty array.
    $admin_emails = $config->get('admin_emails') ?: [];

    // Define the form element for admin emails.
    $form['admin_emails'] = [
    '#type' => 'textarea',
    '#title' => $this->t('Admin Emails'),
    '#description' => $this->t('Enter admin emails, one per line.'),
    '#default_value' => implode("\n", $admin_emails),
  ];

  return parent::buildForm($form, $form_state);
}

  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Get the submitted admin emails from the form state.
    $emails = explode(',', $form_state->getValue('admin_emails',''));

    // Validate each email address using the EmailValidator service.
    foreach ($emails as $email) {
      if (!$this->emailValidator->isValid($email)) {
        // Set a form error if an invalid email is found.
        $form_state->setError($form['admin_emails'], $this->t('Invalid email @value', [
          '@value' => $email,
        ]));
      }
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
   public function submitForm(array &$form, FormStateInterface $form_state) {
    // Save the admin email addresses to the configuration.
    $this->config('custom_emails_config.settings')
      ->set('admin_emails', explode(',', $form_state->getValue('admin_emails')))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
