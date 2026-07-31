# User_MboPasswordReset

Adobe Commerce (Magento 2) module that enforces a mandatory password reset on the **first login** of every newly created MBO admin account.

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
