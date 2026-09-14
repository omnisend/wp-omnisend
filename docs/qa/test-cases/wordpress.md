---
owner: ecom-platforms
last-verified: 2026-09-11
---

# Test cases — wp-omnisend

What PHPUnit/polar-bear specs can't fully express: the oracle, data setup, and
manual steps for each business case. `WP-TC-xxx` IDs are stable, never reused;
each links to its `WP-BC-xxx` parent. Run results (PASS/FAIL/flaky) live in the
QA report — never commit per-run status here.

## WP-BC-001 — OAuth connect / disconnect

| ID        | Case                                                            | Oracle / expected                                                                    | Setup / notes                                                                                                          |
| --------- | --------------------------------------------------------------- | ------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------------------------------------------- |
| WP-TC-001 | Full OAuth connect: landing page → consent → callback           | `omni_send_core_auth_mode=oauth`, `omni_send_core_brand_id` set, admin shows Connected | Requires logged-in `app.omnisend.work` session on a `platform=wordpress` brand; Docker WP or `omni-woo-test.com` only — never Playground |
| WP-TC-002 | Token refresh before expiry                                     | With `omni_send_core_oauth_token_expires_at` forced near-expiry, next API call refreshes silently | `wp option update omni_send_core_oauth_token_expires_at 1`, then trigger any API call                                |
| WP-TC-003 | Consent authorized against wrong-platform brand                 | Plugin rejects with "connected to another platform"; runtime tokens cleared            | e.g. authorize while logged into a `platform=woocommerce` brand — expected failure, not a bug                          |
| WP-TC-004 | Reinstall + reconnect                                           | `plugin uninstall` wipes all `omni_send_core_*` options; fresh connect registers a new DCR client | `wp option list --search='omni_send_core_*'` → empty after uninstall                                                   |

## WP-BC-003 — SDK contact save/update

| ID        | Case                                        | Oracle / expected                                            | Setup / notes                                                            |
| --------- | ------------------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------------------ |
| WP-TC-010 | `get_client(name, version)` → save contact  | Contact appears in brand audience tagged `wordpress`         | Verify via Omnisend API `/v3/contacts?email=…` on testing env            |
| WP-TC-011 | WP user → contact cron sync                   | `wp cron event run omni_send_cron_sync_contacts` syncs users without `omni_send_core_last_sync`; adds `wordpress_roles` prop | Users marked `ERROR` are never retried — delete the meta to retry        |
| WP-TC-012 | Disconnected plugin sends nothing           | SDK calls no-op / error cleanly when `is_connected()` false  | Guard conditions: `is_plugin_active` + `class_exists` + `is_connected()` |

## WP-BC-005 — Tracking snippet

| ID        | Case                              | Oracle / expected                                     | Setup / notes                              |
| --------- | --------------------------------- | ----------------------------------------------------- | ------------------------------------------ |
| WP-TC-020 | Snippet renders when connected    | `launcher-v2.js` tag in page footer on public pages   | View source / DOM check on storefront      |
| WP-TC-021 | No snippet when disconnected      | No snippet markup after disconnect                    | Contrast with WP-TC-020                    |

_Additional areas (products/events/batch — WP-BC-004) get their own section as cases are written._
