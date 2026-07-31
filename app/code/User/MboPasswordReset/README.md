# User_MboPasswordReset

Adobe Commerce (Magento 2) module that enforces a mandatory password reset on the **first login** of every newly created admin user account.

---

## Table of Contents

1. [Overview](#overview)
2. [Requirements](#requirements)
3. [Directory Structure](#directory-structure)
4. [How It Works](#how-it-works)
5. [Sequence Diagram](#sequence-diagram)
6. [Database Changes](#database-changes)
7. [Key Components](#key-components)
8. [Installation](#installation)
9. [Testing Manually](#testing-manually)
10. [Security Considerations](#security-considerations)

---

## Overview

When any new admin user account is created, the module automatically sets a `force_password_change` flag on that user. The next time the user logs in they are intercepted before reaching any admin page and redirected to a dedicated password-reset form. After successfully setting a new password the flag is cleared, the user is logged out, and they are directed back to the login page with a success message.

Existing admin users (created before the module was installed) are **not affected** — their flag defaults to `0`.

---

## Requirements

| Dependency | Version |
|---|---|
| Adobe Commerce / Magento Open Source | 2.4.x |
| PHP | 8.1+ |
| `Magento_User` | any |
| `Magento_Backend` | any |
| `Magento_Security` | any |

---

## Directory Structure

```
app/code/User/MboPasswordReset/
├── registration.php
├── README.md
├── etc/
│   ├── module.xml                                  # module declaration & load sequence
│   ├── db_schema.xml                               # adds user_type + force_password_change to admin_user
│   ├── db_schema_whitelist.json
│   ├── acl.xml                                     # ACL resource for the reset page
│   └── adminhtml/
│       ├── di.xml                                  # two plugins registered here
│       ├── events.xml                              # two event observers
│       └── routes.xml                              # admin route: mbopasswordreset
├── Setup/Patch/Data/
│   └── SetExistingUsersDefaultFlag.php             # sets force_password_change=0 for pre-existing rows
├── Observer/
│   ├── CheckForcePasswordChange.php                # backend_auth_user_login_success → sets session flag
│   └── SetForcePasswordChangeOnCreate.php          # admin_user_save_after → flags new accounts
├── Plugin/
│   ├── EnforcePasswordReset.php                    # aroundDispatch — redirect enforcement + AJAX support
│   └── LoginPasswordResetMessage.php               # afterExecute on Login — injects success message
├── Controller/Adminhtml/Password/
│   ├── Reset.php   (GET)                           # renders the reset form
│   └── Save.php    (POST)                          # validates & persists new password
└── view/adminhtml/
    ├── layout/
    │   └── mbopasswordreset_password_reset.xml
    └── templates/
        └── password/
            └── reset.phtml                         # form with client-side + server-side validation
```

---

## How It Works

### 1 — New User Created

When any admin user is saved (`admin_user_save_after`), the observer `SetForcePasswordChangeOnCreate` checks `$user->isObjectNew()`. If the user is brand-new, a **direct SQL UPDATE** sets `force_password_change = 1` on that `admin_user` row (avoids triggering the model's save cycle again).

```
admin_user.force_password_change  →  1
```

### 2 — Login Interception

After a successful admin login (`backend_auth_user_login_success`), the observer `CheckForcePasswordChange` reads the authenticated user's `force_password_change` value. If it is `1`, a flag is stored in the backend auth session:

```php
$this->authSession->setMboForcePasswordChange(true);
```

If the flag is `0` (existing users, already-reset users), any stale session flag is cleared and login proceeds normally.

### 3 — Per-Request Redirect Enforcement

The plugin `EnforcePasswordReset` wraps `Magento\Backend\App\AbstractAction::dispatch()`. Before each admin controller executes it checks:

- Session is authenticated
- `getMboForcePasswordChange()` is `true`
- The current route is **not** `mbopasswordreset` (the reset page itself)
- The current route is **not** `admin/auth/logout` (so the user can always log out)

If all conditions match the plugin returns early with a redirect to `/admin/mbopasswordreset/password/reset`.

**AJAX requests** (background notification polls, session keepalive, etc.) receive a JSON response instead of an HTML redirect so the admin UI does not display "Something went wrong":

```json
{ "ajaxExpired": 1, "ajaxRedirect": "/admin/mbopasswordreset/password/reset" }
```

### 4 — Password Reset Form

`Controller/Adminhtml/Password/Reset` (GET) renders the form. If the session flag is absent (user navigates to the URL directly after already resetting), they are redirected to the dashboard.

### 5 — Password Save & Flag Clearance

`Controller/Adminhtml/Password/Save` (POST) performs these steps in order:

| Step | Detail |
|---|---|
| CSRF check | `$this->_formKeyValidator->validate($request)` |
| Field presence | All three fields required |
| Confirmation match | `new_password === confirm_password` |
| Complexity | ≥ 7 chars, at least 1 letter + 1 digit |
| Not same as current | `new_password !== current_password` |
| Identity verification | `$userModel->verifyIdentity($currentPassword)` |
| Persist new password | `$userModel->setPassword($newPassword)->save()` |
| Clear DB flag | `force_password_change = 0` saved atomically with the password |
| Clear session flag | `$authSession->unsMboForcePasswordChange()` |
| Logout | `$this->_auth->logout()` |
| Redirect | `→ /admin/auth/login?password_reset=1` |

### 6 — Success Message on Login Page

Because `logout()` deletes the admin session cookie, any message stored in the session before the redirect would be lost. To work around this, the redirect carries `?password_reset=1` as a URL parameter. The plugin `LoginPasswordResetMessage` intercepts the Login controller's `execute()` response, reads the parameter, and injects the success message **into the same request** (before the page renders):

> *Password updated successfully. Please log in with your new password.*

### 7 — Existing Users

The data patch `SetExistingUsersDefaultFlag` ensures every pre-existing `admin_user` row has `force_password_change = 0`. The DB column is also defined with `DEFAULT 0`, so any row created outside the observer is inherently safe.

---

## Sequence Diagram

```
New Admin User          Login Controller     Auth Model     Observer            Session      EnforceReset Plugin    Reset Controller   Save Controller    DB
     |                        |                  |         (CheckForce)            |                  |                    |                  |            |
     |--- POST /admin/login ->|                  |                |                |                  |                    |                  |            |
     |                        |--- login() ----->|                |                |                  |                    |                  |            |
     |                        |                  |-- dispatch(login_success) ----->|                  |                    |                  |            |
     |                        |                  |                |-- setMboForce(true) ------------->|                    |                  |            |
     |<-- redirect /dashboard-|                  |                |                |                  |                    |                  |            |
     |                        |                  |                |                |                  |                    |                  |            |
     |--- GET /admin/dashboard --------------------------------------------------------- aroundDispatch|                    |                  |            |
     |                        |                  |                |                |-- getMboForce? -->|                    |                  |            |
     |                        |                  |                |                |<-- true ----------|                    |                  |            |
     |<-- redirect /mbopasswordreset/password/reset (or JSON ajaxRedirect for XHR) --------|           |                  |            |
     |                        |                  |                |                |                  |                    |                  |            |
     |--- GET /mbopasswordreset/password/reset ----------------------------------------->|            |                  |            |
     |<-- reset form (current / new / confirm password fields) ----------------------|                 |                  |            |
     |                        |                  |                |                |                  |                    |                  |            |
     |--- POST /mbopasswordreset/password/save --------------------------------------------------- validate + save ------>|            |
     |                        |                  |                |                |                  |                    |-- setPassword -->|            |
     |                        |                  |                |                |                  |                    |-- force_pw=0 --->|            |
     |                        |                  |                |                |<-- unsMboForce --|                    |                  |            |
     |                        |                  |<-- logout() ---|                |                  |                    |                  |            |
     |<-- redirect /admin/auth/login?password_reset=1                                                                     |                  |            |
     |                        |                  |                |                |                  |                    |                  |            |
     |--- GET /admin/auth/login?password_reset=1 -> LoginPasswordResetMessage plugin injects success message              |                  |            |
     |<-- Login page with green success banner: "Password updated successfully..."                                         |                  |            |
     |                        |                  |                |                |                  |                    |                  |            |
     |--- POST /admin/login (new password) ------>|                |                |                  |                    |                  |            |
     |<-- Dashboard (force_password_change=0, session flag absent, no redirect) -----|                 |                  |            |
```

---

## Database Changes

Two columns are added to the existing `admin_user` table via `db_schema.xml`:

| Column | Type | Default | Nullable | Purpose |
|---|---|---|---|---|
| `user_type` | `varchar(32)` | NULL | YES | Optional user category label (e.g. `'mbo'`). Available for other modules. |
| `force_password_change` | `smallint` | `0` | NO | `1` = must reset on next login, `0` = no restriction |

No new tables are created.

---

## Key Components

### `etc/adminhtml/events.xml`

| Event | Observer | Purpose |
|---|---|---|
| `backend_auth_user_login_success` | `CheckForcePasswordChange` | Reads DB flag; sets `MboForcePasswordChange` in session if `1` |
| `admin_user_save_after` | `SetForcePasswordChangeOnCreate` | On new user save, sets `force_password_change = 1` via direct SQL |

### `etc/adminhtml/di.xml`

| Plugin target | Plugin class | Method | Purpose |
|---|---|---|---|
| `Magento\Backend\App\AbstractAction` | `EnforcePasswordReset` | `aroundDispatch` | Redirects all non-exempt admin requests; returns JSON for AJAX |
| `Magento\Backend\Controller\Adminhtml\Auth\Login` | `LoginPasswordResetMessage` | `afterExecute` | Injects success message when `?password_reset=1` is present |

### Controllers

| Route | Class | Method | Description |
|---|---|---|---|
| `mbopasswordreset/password/reset` | `Password\Reset` | GET | Renders the reset form; redirects to dashboard if flag is absent |
| `mbopasswordreset/password/save` | `Password\Save` | POST | Validates, saves new password, clears flag, logs out, redirects |

### Password Validation Rules (server-side enforced, client-side mirrored)

- All three fields required (current, new, confirm)
- New password ≥ 7 characters
- At least one letter (`[a-zA-Z]`) and one digit (`[0-9]`)
- New password must differ from current password
- Confirmation must match new password
- Current password verified via `User::verifyIdentity()`

---

## Installation

```bash
# 1. Place the module
cp -r User/MboPasswordReset /var/www/html/magento2/app/code/User/

# 2. Enable
php bin/magento module:enable User_MboPasswordReset

# 3. Apply schema + data patch
php bin/magento setup:upgrade

# 4. Recompile DI
php bin/magento setup:di:compile

# 5. Flush cache
php bin/magento cache:flush
```

---

## Testing Manually

### Verify columns exist

```sql
SHOW COLUMNS FROM admin_user WHERE Field IN ('user_type', 'force_password_change');
```

### Create a new admin user and test the flow

1. Log in as a super-admin and go to **Admin → System → All Users → Add New User**.
2. Fill in required fields and save.
3. Verify the flag was set:
   ```sql
   SELECT username, force_password_change FROM admin_user ORDER BY user_id DESC LIMIT 1;
   -- force_password_change should be 1
   ```
4. Log out and log in as the new user.
5. You will be **redirected to** `/admin/mbopasswordreset/password/reset`.
6. Try navigating to `/admin/dashboard` — you will be redirected back.
7. Submit the reset form with a valid new password.
8. You will land on the **login page with the success message**: *Password updated successfully. Please log in with your new password.*
9. Log in with the new password — you reach the dashboard with no redirect.

### Verify flag was cleared

```sql
SELECT username, force_password_change FROM admin_user WHERE username = 'youruser';
-- force_password_change should be 0
```

### Verify existing users are unaffected

```sql
SELECT username, force_password_change FROM admin_user;
-- All pre-existing rows should show 0
```

---

## Security Considerations

| Concern | Mitigation |
|---|---|
| CSRF | Form key validated on every POST via `_formKeyValidator` |
| Session fixation | User is fully logged out after password change |
| Navigation bypass | `EnforcePasswordReset::aroundDispatch` enforces on every admin request |
| AJAX error popup | XHR requests receive `ajaxRedirect` JSON instead of HTML redirect |
| Direct URL access | `Reset` controller checks session flag; redirects to dashboard if absent |
| Password reuse | Server-side check: new password must differ from current |
| Password complexity | Minimum 7 chars, at least one letter and one digit |
| Infinite redirect loop | `mbopasswordreset` and `admin/auth/logout` routes whitelisted in plugin |
| Success message loss on logout | Carried via `?password_reset=1` URL parameter; injected by `LoginPasswordResetMessage` plugin during the login page render |


---

## Table of Contents

1. [Overview](#overview)
2. [Requirements](#requirements)
3. [Directory Structure](#directory-structure)
4. [How It Works](#how-it-works)
5. [Sequence Diagram](#sequence-diagram)
6. [Database Changes](#database-changes)
7. [Key Components](#key-components)
8. [Installation](#installation)
9. [Testing Manually](#testing-manually)
10. [Security Considerations](#security-considerations)

---

## Overview

When a new MBO user account is created in the Adobe Commerce admin panel, the module automatically sets a `force_password_change` flag on that user. The next time the user logs in, they are intercepted before reaching any admin page and redirected to a dedicated password-reset form. After successfully setting a new password the flag is cleared, the user is logged out, and they are directed back to the login page.

Existing admin users and non-MBO users are **not affected**.

---

## Requirements

| Dependency | Version |
|---|---|
| Adobe Commerce / Magento Open Source | 2.4.x |
| PHP | 8.1+ |
| `Magento_User` | any |
| `Magento_Backend` | any |
| `Magento_Security` | any |

---

## Directory Structure

```
app/code/User/MboPasswordReset/
├── registration.php
├── README.md
├── etc/
│   ├── module.xml                              # module declaration & load sequence
│   ├── db_schema.xml                           # adds user_type + force_password_change columns
│   ├── db_schema_whitelist.json
│   ├── acl.xml                                 # ACL resource for the reset page
│   └── adminhtml/
│       ├── di.xml                              # plugin on AbstractAction::dispatch
│       ├── events.xml                          # two event observers
│       └── routes.xml                          # admin route: mbopasswordreset
├── Setup/Patch/Data/
│   └── SetExistingUsersDefaultFlag.php         # sets force_password_change=0 for pre-existing rows
├── Observer/
│   ├── CheckForcePasswordChange.php            # backend_auth_user_login_success
│   └── SetForcePasswordChangeOnCreate.php      # admin_user_save_after
├── Plugin/
│   └── EnforcePasswordReset.php                # aroundDispatch — redirect enforcement
└── Controller/Adminhtml/Password/
│   ├── Reset.php   (GET)                       # renders the reset form
│   └── Save.php    (POST)                      # validates & persists new password
└── view/adminhtml/
    ├── layout/
    │   └── mbopasswordreset_password_reset.xml
    └── templates/
        └── password/
            └── reset.phtml                     # form template with client-side validation
```

---

## How It Works

### 1 — New MBO User Created

When any admin user is saved (`admin_user_save_after`), the observer `SetForcePasswordChangeOnCreate` checks:

- `$user->isObjectNew()` — only acts on brand-new rows
- `$user->getData('user_type') === 'mbo'` — only MBO accounts

If both conditions are true, a **direct SQL UPDATE** sets `force_password_change = 1` on the `admin_user` row (avoids triggering the model's save cycle again).

```
admin_user.force_password_change  →  1
admin_user.user_type              →  'mbo'
```

### 2 — Login Interception

After a successful admin login (`backend_auth_user_login_success`), the observer `CheckForcePasswordChange` reads the authenticated user's data. If `user_type = 'mbo'` **and** `force_password_change = 1`, it stores a flag in the backend session:

```php
$this->authSession->setMboForcePasswordChange(true);
```

### 3 — Per-Request Redirect Enforcement

The plugin `EnforcePasswordReset` wraps `Magento\Backend\App\AbstractAction::dispatch()` with an `aroundDispatch`. Before each admin controller executes, it checks:

```
authSession->isLoggedIn()              → true
authSession->getMboForcePasswordChange() → true
request->getModuleName()               → NOT 'mbopasswordreset' AND NOT 'admin/auth/logout'
```

If all conditions match, the plugin short-circuits the request and returns a redirect to `/admin/mbopasswordreset/password/reset`, preventing the user from reaching any other admin page.

### 4 — Password Reset Form

`Controller/Adminhtml/Password/Reset` (GET) renders the form only if the session flag is set. Navigating to the reset URL without the flag redirects to the dashboard (safety guard against direct URL access).

### 5 — Password Save & Flag Clearance

`Controller/Adminhtml/Password/Save` (POST) performs, in order:

| Step | Detail |
|---|---|
| CSRF check | `$this->_formKeyValidator->validate($request)` |
| Field presence | all three fields required |
| Confirmation match | `new_password === confirm_password` |
| Complexity | ≥ 7 chars, at least 1 letter + 1 digit |
| Not same as current | `new_password !== current_password` |
| Identity verification | `$userModel->verifyIdentity($currentPassword)` |
| Persist new password | `$userModel->setPassword($newPassword)->save()` |
| Clear DB flag | `force_password_change = 0` saved with the model |
| Clear session flag | `$authSession->unsMboForcePasswordChange()` |
| Logout | `$this->_auth->logout()` |
| Redirect | → `/admin/auth/login` with success message |

### 6 — Existing Users

The data patch `SetExistingUsersDefaultFlag` ensures every pre-existing `admin_user` row has `force_password_change = 0`. The DB column itself is defined with `DEFAULT 0`, so any future rows not going through the observer are also safe.

---

## Sequence Diagram

```
MBO Admin (new)           Login Controller    Auth Model    Observer (CheckForce)    Session    Plugin (aroundDispatch)    Reset Controller    Save Controller    DB
       |                        |                  |                 |                  |                  |                        |                  |            |
       |--- POST /admin/login ->|                  |                 |                  |                  |                        |                  |            |
       |                        |--- login() ----->|                 |                  |                  |                        |                  |            |
       |                        |                  |--- authenticate ----------------------------------------->|                  |                  |            |
       |                        |                  |<-- user {mbo, force=1} ----------------------------------|                  |                  |            |
       |                        |                  |-- dispatch(backend_auth_user_login_success) ->|          |                  |                  |            |
       |                        |                  |                 |--- setMboForcePasswordChange(true) ->|  |                  |                  |            |
       |                        |<-- redirect dashboard -------------|                  |                  |                        |                  |            |
       |<-- redirect /dashboard-|                  |                 |                  |                  |                        |                  |            |
       |                        |                  |                 |                  |                  |                        |                  |            |
       |--- GET /admin/dashboard ------------------------------------ (any admin page) ------------------>|                        |                  |            |
       |                        |                  |                 |                  |--- getMboForce? ->|                        |                  |            |
       |                        |                  |                 |                  |<-- true ----------|                        |                  |            |
       |<-- redirect /mbopasswordreset/password/reset --------------|                  |                  |                        |                  |            |
       |                        |                  |                 |                  |                  |                        |                  |            |
       |--- GET /mbopasswordreset/password/reset -------------------------------------------------------->|                        |                  |            |
       |<-- reset form --------------------------------------------------------------------------------------------------------------------------|            |
       |                        |                  |                 |                  |                  |                        |                  |            |
       |--- POST /mbopasswordreset/password/save (current, new, confirm) -------------------------------->|                        |-- validate, save ->|           |
       |                        |                  |                 |                  |                  |                        |                  |-- UPDATE ->|
       |                        |                  |                 |                  |<-- unsMboForce --|                        |                  |            |
       |                        |                  |<-- logout() ----|                  |                  |                        |                  |            |
       |<-- redirect /admin/auth/login + success message ------------|                  |                  |                        |                  |            |
       |                        |                  |                 |                  |                  |                        |                  |            |
       |--- GET /admin/auth/login (new password, normal login) ----->|                 |                  |                        |                  |            |
       |<-- dashboard (force_password_change=0, no redirect) --------|                  |                  |                        |                  |            |
```

---

## Database Changes

Two columns are added to the existing `admin_user` table via `db_schema.xml`:

| Column | Type | Default | Nullable | Purpose |
|---|---|---|---|---|
| `user_type` | `varchar(32)` | NULL | YES | Identifies user category (e.g. `'mbo'`) |
| `force_password_change` | `smallint` | `0` | NO | `1` = must reset on next login |

No new tables are created.

---

## Key Components

### `etc/adminhtml/events.xml`

| Event | Observer | Purpose |
|---|---|---|
| `backend_auth_user_login_success` | `CheckForcePasswordChange` | Sets session flag for MBO users |
| `admin_user_save_after` | `SetForcePasswordChangeOnCreate` | Flags brand-new MBO accounts |

### `etc/adminhtml/di.xml`

| Plugin target | Plugin class | Type | Purpose |
|---|---|---|---|
| `Magento\Backend\App\AbstractAction` | `EnforcePasswordReset` | `aroundDispatch` | Redirects all non-exempt requests when flag is set |

### Controllers

| Route | Class | Method | Description |
|---|---|---|---|
| `mbopasswordreset/password/reset` | `Password\Reset` | GET | Render reset form |
| `mbopasswordreset/password/save` | `Password\Save` | POST | Validate & persist new password |

### Password Validation Rules (server-side + client-side)

- All three fields required
- New password ≥ 7 characters
- At least one letter (`[a-zA-Z]`)
- At least one digit (`[0-9]`)
- New password ≠ current password
- Confirmation must match new password
- Current password verified against stored hash via `User::verifyIdentity()`

---

## Installation

```bash
# 1. Place the module
cp -r User/MboPasswordReset /var/www/html/magento2/app/code/User/

# 2. Enable
php bin/magento module:enable User_MboPasswordReset

# 3. Apply schema + data patch
php bin/magento setup:upgrade

# 4. Recompile DI
php bin/magento setup:di:compile

# 5. Flush cache
php bin/magento cache:flush
```

---

## Testing Manually

### Create a test MBO user

```sql
-- Verify the columns are present
SHOW COLUMNS FROM admin_user WHERE Field IN ('user_type','force_password_change');
```

1. Go to **Admin → System → All Users → Add New User**
2. Fill in required fields and set **User Type = mbo** (or update via SQL: `UPDATE admin_user SET user_type='mbo', force_password_change=1 WHERE username='testmbo';`)
3. Log out and log back in as the MBO user.
4. You should be redirected to `/admin/mbopasswordreset/password/reset`.
5. Try navigating to `/admin/dashboard` — you should be redirected back to the reset page.
6. Submit the reset form with a valid new password.
7. You should see the success message and be redirected to the login page.
8. Log in with the new password — you should reach the dashboard without redirection.

### Verify the flag was cleared

```sql
SELECT username, user_type, force_password_change FROM admin_user WHERE username = 'testmbo';
-- force_password_change should be 0
```

### Verify existing users are unaffected

```sql
SELECT username, force_password_change FROM admin_user WHERE user_type != 'mbo' OR user_type IS NULL;
-- All rows should show force_password_change = 0
```

---

## Security Considerations

| Concern | Mitigation |
|---|---|
| CSRF | Form key validated on every POST via `_formKeyValidator` |
| Session fixation | User is fully logged out after password change |
| Brute-force on current password | Relies on Magento's built-in login attempt throttling |
| Direct URL bypass | `Reset` controller checks session flag; redirects to dashboard if absent |
| Navigation bypass | `EnforcePasswordReset::aroundDispatch` enforces on every admin request |
| Password reuse | Server-side check: new password must differ from current |
| Password complexity | Minimum 7 chars, at least one letter and one digit (mirrors Magento admin policy) |
| Infinite redirect loop | `mbopasswordreset` and `admin/auth/logout` routes explicitly whitelisted in plugin |
