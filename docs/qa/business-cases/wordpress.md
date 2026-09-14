---
owner: ecom-platforms
last-verified: 2026-09-14
---

# Business cases — wp-omnisend (core WordPress plugin)

Intent + acceptance criteria per case. `WP-BC-xxx` IDs are stable and never
reused; specs here and in polar-bear reference them in test titles. Add new cases
at the end.

Seeded from the Project-53 QA run (OAuth migration, #179 + #203/#204/#205/#207/
#209/#210); see the QA report for per-run results — they don't live here.

| ID        | Business case                                                                                  | Acceptance criteria                                                                                                                             | Coverage                     |
| --------- | ---------------------------------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------- | ---------------------------- |
| WP-BC-001 | Merchant connects a WordPress site to Omnisend via OAuth (new install)                         | DCR client registered, code exchanged, brand id + tokens stored, `auth_mode=oauth`, admin shows Connected, contact-sync cron scheduled              | PHPUnit + manual E2E         |
| WP-BC-002 | Reconnect rules by brand platform                                                              | Brand already `platform=wordpress` reconnects without a brand write / "already connected" 400; other-platform brand is rejected and tokens cleared | PHPUnit + manual E2E         |
| WP-BC-003 | Create a new Omnisend account from the landing page                                            | "Create new account" opens registration (new tab, `utm_source=wordpress_plugin`); OAuth start/register URLs reject missing/invalid nonce           | Manual E2E + PHPUnit         |
| WP-BC-004 | Landing page polls connection status and reloads when connected                                | `omnisend_connection_status` AJAX polled ~2 s after connect click; reloads to connected state; nonce/capability enforced                           | Manual E2E + PHPUnit         |
| WP-BC-005 | OAuth state is valid for 1 h and single-use                                                    | Callback inside the window accepted; tampered/expired/missing/replayed state rejected                                                            | Manual                       |
| WP-BC-006 | Connection failures surface actionable errors                                                  | 401/403 perms/410 retired version/429/5xx/bad JSON/transport each map to a specific message; tokens cleared on failure                             | PHPUnit + manual             |
| WP-BC-007 | Existing API-key installs keep working                                                         | `omni_send_core_api_key` without `auth_mode` resolves to `api_key`; `Omnisend-API-Key` + `Omnisend-Version` headers; no re-connect prompt           | Manual regression            |
| WP-BC-008 | Auto-connect using the Omnisend-for-WooCommerce plugin's API key                               | With the Woo plugin connected, activating this plugin reuses its key, stores the brand id, marks connected; idempotent                            | Manual regression            |
| WP-BC-009 | WP users sync to Omnisend contacts via cron                                                    | New users get tag `wordpress` + `wordpress_roles` property; `omni_send_core_last_sync` recorded                                                    | Manual + PHPUnit             |
| WP-BC-010 | SDK `get_contact_by_email`                                                                     | Existing → Contact with `id`; missing → typed error (`get_wp_error()`), no fatal                                                                  | PHPUnit + manual             |
| WP-BC-011 | SDK `send_customer_event` → `/api/events`                                                      | 2xx on testing; event with mapped properties lands on the brand                                                                                   | PHPUnit + manual             |
| WP-BC-012 | SDK products / categories / batches                                                            | CRUD on `/api/products`, `/api/product-categories`, `/api/batches`; category title ≤255 accepted, 256 rejected                                      | PHPUnit + manual             |
| WP-BC-013 | OAuth token refresh                                                                            | Expired access token refreshes via `refresh_token` silently; refresh failure clears tokens and prompts reconnect                                  | Manual                       |
| WP-BC-014 | Landing page rendering (incl. Hostinger variant)                                               | "Create new account" + "Connect your account" (`target=_blank`); Hostinger flag keeps "Explore Omnisend"; no API-key form                            | Manual visual                |
| WP-BC-015 | Disconnect / uninstall / failed callback clean up all state                                    | Disconnect removes `omni_send_core_*` (keeps DCR client creds); uninstall wipes everything; `error=`/bad-state callback clears tokens + auth_mode    | Manual + PHPUnit             |
| WP-BC-016 | SDK V1 public surface stays backward compatible (3rd-party plugins)                            | Full PHPUnit suite green; public signatures unchanged except intentional breaks (e.g. nullable `GetContactResponse::$contact`)                     | PHPUnit + signature diff     |
| WP-BC-017 | Retained `/v3` calls keep legacy headers                                                       | API-key `connect_store` for existing brands and `/v3/orders` still use `X-API-Key` against `OMNISEND_CORE_API_V3`; no `Omnisend-Version` added      | Code review + PHPUnit        |
| WP-BC-018 | Build / lint / unit pipeline                                                                   | `composer install`, `phpunit`, `phpcs`, `npm run build`, `zip_plugin.sh` all pass on a change head                                                | CI                           |
| WP-BC-019 | Email lifecycle events behind `omni_send_core_email_service_opt_in` (PR #164, pending)         | register/password-change/email-change events sent only when opt-in enabled; option deleted on disconnect                                          | Not testable until rebased   |
| WP-BC-020 | OAuth client re-registration when the store has no access token (abandoned consent, URL change) | No token → new `POST /oauth2/register` with current redirect URI; valid token → no re-registration; failed registration keeps the previous client | PHPUnit + manual             |
| WP-BC-021 | Contact writes merge existing tags; write-by-id uses PATCH                                     | create `[a]` + save-by-email `[b]` + save-by-id `[c]` → contact ends `[a,b,c]`; PATCH `/api/contacts/{id}`; no lookup GET when no tags passed       | PHPUnit + manual             |
| WP-BC-022 | API-key connection form / REST `omnisend/v1/connect` removed                                   | No key input on the landing page; REST route 404; OAuth is the only new-connection path                                                           | Manual                       |
| WP-BC-023 | Connected state is a reliable contract for integrating plugins                                 | `Omnisend::is_connected()` + option state reflect reality; consumers can't send while disconnected/not installed                                   | `tests/`                     |
| WP-BC-024 | Front-end tracking snippet injected on storefront pages                                        | Snippet renders on public pages when connected, absent when disconnected                                                                          | —                            |
| WP-BC-025 | Compatibility headers stay honest — `Requires PHP` / `Tested up to` reflect verified support   | wordpress.org install warnings match what was actually tested (see wp-tested-up-to-bump workflow)                                                 | —                            |

_Gaps (—) are candidates for PHPUnit coverage here or polar-bear specs downstream._
