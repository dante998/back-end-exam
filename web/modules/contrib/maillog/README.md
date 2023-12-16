# Maillog

The Maillog module provides a method of keeping archival copies of all emails
sent through the site.


## Features

* All emails being sent by the site may have a copy stored in the database for
  later review.
* All email delivery may be halted, preventing a site from sending out emails
  in situations where that might not be needed, e.g. for a local development
  copy of the site.
* If the [MailSystem module](https://www.drupal.org/project/mailsystem) is
  installed it is possible to control which of the installed email modules will
  be used to send messages from Maillog's settings page, mirroring MailSystem's
  functionality.


## Configuration

1. On the People Permissions administration page (Administer -> People
  -> Permissions) there are three permissions to control:

  * The "Administer Maillog" permission allows users to access the settings
    page to control the module's options.
  * The "View Maillog" permission allows users to access the Maillog list page
    at admin/reports/maillog.
  * The "Delete Entries from the log" permission allows users to delete items
    from the log page.

2. The main administrative page controls the module's settings page:
   admin/config/development/maillog


## Troubleshooting / known issues

If the email is not being logged then the site's default email system is not
configured correctly. It may help to hardcode the use of Maillog by adding these
lines to the end of the site's settings.php file:

    // Use Maillog for delivering system emails.
    $config['system.mail']['interface']['default'] = 'maillog';

It is recommended to use the MailSystem module to help with this, and again
its settings can be hardcoded in the settings.php file:

    // MailSystem: Use Maillog for delivering system emails.
    $config['mailsystem.settings']['defaults']['sender'] = 'maillog';
    $config['mailsystem.settings']['defaults']['formatter'] = 'maillog';

It may also be useful to hardcode Maillog's individual settings:

    // Maillog.
    // Don't actually deliver emails.
    $config['maillog.settings']['send'] = FALSE;
    // Log all messages.
    $config['maillog.settings']['log'] = TRUE;
    // Don't display messages when they are logged.
    $config['maillog.settings']['verbose'] = FALSE;


## Related modules

Some similar modules that are available include:

* [Reroute Email](http://drupal.org/project/reroute_email) - reroutes outbound
  emails to a specific destination address.


## Credits / contact

Maintained by [Miro Dietiker](https://www.drupal.org/u/miro_dietiker), [Sascha
Grossenbacher](https://www.drupal.org/u/berdir) and [Damien
McKenna](https://www.drupal.org/u/damiemckenna). Initial port to Drupal 8 by
[Antonio Sferlazza](https://www.drupal.org/u/tonnosf).

The best way to contact the authors is to submit an issue, be it a support
request, a feature request or a bug report, in the [project issue
queue](https://www.drupal.org/project/issues/maillog).
