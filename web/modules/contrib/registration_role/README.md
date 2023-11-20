# Registration Role README

Project page: [`drupal.org/project/registration_role`](https://www.drupal.org/project/registration_role)

Registration role lets an administrator select a role (or multiple roles) to automatically assign to new users. The selected role (or roles) will be assigned to new registrants.

_Be sure these roles do not have any privileges that should not be given out to just anyone who registers._

Because we shouldn't give away any real abilities above an authenticated user by default, this module really has only a few known useful applications:

1. If you want to assign all people who sign up after (or before) a certain time to a role to distinguish them— simply set the roles as appropriate at the appropriate time.
2. If you have multiple sites with a shared user database table, and you want to assign users a role based on the site at which they register.
3. If you want people who register themselves to have a distinct role from users invited by an administrator.
4. If paired with a custom or contrib token system to restrict registration.
5. If you want to make some fields on a content creation forms required for new users with the help of [required\_by\_role module](https://www.drupal.org/project/required_by_role) so existing or selected users _without_ that role can skip filling values for these fields.
6. If you want a "regular user" role which can have permissions which are _not_ inherited by other roles, as authenticated user role's permissions are. This can be useful for a "Request publication" content moderation transition which regular users should have but editor users don't want to see [a button for](https://www.drupal.org/project/workflow_buttons).

Or undoubtedly you can come up with your own creative uses!

Drupal roles exist primarily for access permissions, but modules such as [mass contact](http://drupal.org/project/mass_contact) also use roles to act on a group users.

Originally based on a [code snippet by Pauly Jura](http://drupal.org/node/28379#comment-132430). (It actually does less than the snippet, but it is a module and has a settings administration page!)

See [Auto-assign role module](http://drupal.org/project/autoassignrole) for a more comprehensive take on role assignment, including allowing a user to select their own role.

Registration role is a very lightweight module which does not install any database tables.

Created and co-maintained by Benjamin Melançon of Agaric Technology Collective, https://agaric.coop
