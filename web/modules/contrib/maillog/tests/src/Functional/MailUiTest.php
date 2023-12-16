<?php

namespace Drupal\Tests\maillog\Functional;

use Drupal\maillog\Plugin\Mail\Maillog;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the maillog plugin user interface.
 *
 * @group maillog
 */
class MailUiTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['maillog', 'user', 'system', 'views', 'contact'];

  /**
   * Define the default theme used for all tests.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Use the maillog mail plugin.
    $this->config('system.mail')->set('interface.default', 'maillog')->save();
    // The system.site.mail setting goes into the From header of outgoing mails.
    $this->config('system.site')->set('mail', 'simpletest@example.com')->save();

    // Disable e-mail sending.
    $this->config('maillog.settings')
      ->set('send', FALSE)
      ->save();
  }

  /**
   * Tests logging mail with maillog module.
   */
  public function testLogging() {
    $mail = \Drupal::service('plugin.manager.mail')->mail('maillog', 'ui_test', 'test@example.com', \Drupal::languageManager()->getCurrentLanguage(), [], 'me@example.com', FALSE);
    $mail['subject'] = 'This is a test subject.';
    $mail['body'] = 'This message is a test email body.';

    // Send the prepared email.
    $sender = new Maillog();
    $sender->mail($mail);

    // Create a user with valid permissions and go to the maillog overview page.
    $permissions = [
      'administer maillog',
      'view maillog',
    ];
    $this->drupalLogin($this->drupalCreateUser($permissions));
    $this->drupalGet('admin/reports/maillog');
    $this->assertSession()->statusCodeEquals(200);

    // Assert some values and click the subject link.
    $this->assertSession()->pageTextContains('simpletest@example.com');
    $this->assertSession()->pageTextContains('test@example.com');
    $this->clickLink('This is a test subject.');
    $this->assertSession()->pageTextContains('This message is a test email body.');

    // Test clear log.
    $this->drupalGet('admin/config/development/maillog');
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([], 'Clear all maillog entries');
    $this->assertSession()->statusCodeEquals(200);
    $this->submitForm([], 'Clear');
    $this->assertSession()->statusCodeEquals(200);
    $this->drupalGet('admin/reports/maillog');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextNotContains('simpletest@example.com');
    $this->assertSession()->pageTextContains('There are no mail logs in the database.');
  }

  /**
   * Checks the drupal_set_message() for disabled mail sending.
   */
  public function testNotice() {
    // Create a user with valid permissions and a recipient for a message.
    $recipient = $this->drupalCreateUser();
    $permissions = [
      'access user contact forms',
      'access user profiles',
      'administer maillog',
    ];
    $this->drupalLogin($this->drupalCreateUser($permissions));

    // Send the recipient a message and check the expected notice.
    $edit = [
      'subject[0][value]' => 'Test Message',
      'message[0][value]' => 'This is a test.',
    ];
    $this->drupalGet('user/' . $recipient->id() . '/contact');
    $this->submitForm($edit, 'Send message');
    $this->clickLink('here');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->titleEquals('Maillog Settings | Drupal');
  }

  /**
   * Tests verbose output after send an email.
   */
  public function testVerboseOutput() {
    // Create a user with valid permissions.
    $account = $this->drupalCreateUser();
    $this->drupalLogin($this->drupalCreateUser([
      'access user contact forms',
      'access user profiles',
      'view maillog',
    ]));

    // Load the contact form.
    $this->drupalGet('user/' . $account->id() . '/contact');
    $this->assertSession()->statusCodeEquals(200);

    // Send the message.
    $edit = [
      'subject[0][value]' => 'Test Message',
      'message[0][value]' => 'This is a test.',
    ];
    $this->submitForm($edit, 'Send message');
    $this->assertSession()->statusCodeEquals(200);

    // Assert the verbose output.
    $this->assertSession()->pageTextContains('A mail has been sent:');
    $this->assertSession()->responseContains('[To] => ' . $account->getAccountName() . '@example.com');
    $this->assertSession()->responseContains('[Header] => Array');
    $this->assertSession()->responseContains('[X-Mailer] =&gt; Drupal');
    $this->assertSession()->responseContains('[Content-Type] =&gt; text/plain; charset=UTF-8; format=flowed; delsp=yes');
    $this->assertSession()->responseContains('[Body] => Hello ' . $account->getAccountName());

    // Set verbose to false.
    $this->config('maillog.settings')->set('verbose', FALSE)->save();
    // Send another message.
    $this->drupalGet('user/' . $account->id() . '/contact');
    $this->assertSession()->statusCodeEquals(200);
    $edit = [
      'subject[0][value]' => 'Test Message',
      'message[0][value]' => 'This is a test.',
    ];
    $this->submitForm($edit, 'Send message');
    $this->assertSession()->statusCodeEquals(200);

    // Assert there is no output.
    $this->assertSession()->pageTextNotContains('A mail has been sent:');
    $this->assertSession()->responseNotContains('[To] => ' . $account->getAccountName() . '@example.com');
    $this->assertSession()->responseNotContains('[Header] => Array');

    // Tests that users without permission cannot see verbose output.
    $this->config('maillog.settings')->set('verbose', TRUE)->save();
    $this->drupalLogin($this->drupalCreateUser([
      'access user contact forms',
      'access user profiles',
    ]));

    $this->drupalGet('user/' . $account->id() . '/contact');
    $this->assertSession()->statusCodeEquals(200);
    $edit = [
      'subject[0][value]' => 'Test Message',
      'message[0][value]' => 'This is a test.',
    ];
    $this->submitForm($edit, 'Send message');
    $this->assertSession()->statusCodeEquals(200);

    // Assert there is no output.
    $this->assertSession()->pageTextNotContains('A mail has been sent:');
    $this->assertSession()->responseNotContains('[To] => ' . $account->getAccountName() . '@example.com');
    $this->assertSession()->responseNotContains('[Header] => Array');
  }

}
