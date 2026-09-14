---
owner: ecom-platforms
last-verified: 2026-09-09
---

# Test cases — wp-omnisend

What PHPUnit/polar-bear specs can't fully express: the oracle, data setup, and
manual steps for each business case. `WP-TC-xxx` IDs are stable, never reused;
each links to its `WP-BC-xxx` parent. Run results (PASS/FAIL/flaky) live in the
QA report — never commit per-run status here.

Seeded from the Project-53 QA run (2026-09): WP-TC-001–029 re-key that run's
TC-01–29, grouped under their `WP-BC-*` parents (so numbering is not sequential
in this file). WP-TC-030+ were appended after.

Environment for manual cases: local Docker WordPress (`localhost:8080`) +
`./zip_plugin.sh test` build pointed at testing (`api.omnisend.work` /
`app.omnisend.work`), or the shared Woo test store. OAuth needs an
`app.omnisend.work` login on a `platform=wordpress` brand — never Playground.

## WP-BC-001 — OAuth connect (new install)

| ID        | Case                                                              | Oracle / expected                                                                                                   | Setup / notes                                                                                          |
| --------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------- |
| WP-TC-001 | Full OAuth connect: landing → DCR → consent → callback            | `POST /oauth2/register` → `authorize` → consent → callback; brand id + 64-char tokens stored, `auth_mode=oauth`, Connected page, sync cron scheduled | Fresh WP install; logged-in `app.omnisend.work` session on a `platform=wordpress` (or `""`) brand       |

## WP-BC-002 — Reconnect rules by brand platform

| ID        | Case                                                            | Oracle / expected                                                                    | Setup / notes                                                                   |
| --------- | --------------------------------------------------------------- | ------------------------------------------------------------------------------------ | -------------------------------------------------------------------------------- |
| WP-TC-002 | Reconnect a brand already `platform=wordpress` (reinstall first) | No `POST /api/brands/current` write, no "already connected" 400; connected + sync scheduled | Uninstall clears options but brand stays `wordpress`; verify via `GET /api/brands/current` |
| WP-TC-003 | Consent authorized against a wrong-platform brand (e.g. Woo)    | Rejected: "connected to another platform (woocommerce)"; oauth tokens + `auth_mode` cleared, client creds kept | Authorize while logged into a `platform=woocommerce` brand — expected failure, not a bug |

## WP-BC-003 — Create new account from landing page

| ID        | Case                                                        | Oracle / expected                                                                                              | Setup / notes                                                                                     |
| --------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------------------- |
| WP-TC-004 | "Create new account" → registration → consent              | New tab → `app.omnisend.work/ecom/registration/start?registration_redirect_url=…&utm_source=wordpress_plugin`; after signup, connect completes | Test-email inbox + phone-verification bypass: see the shared "Registering a new Omnisend test account/brand" note in the agent knowledge base — not committed to this public repo. Known app-side gap: `registration_redirect_url` is not honoured after onboarding — finish via "Connect your account" |
| WP-TC-005 | `?omnisend_oauth=register`/`=connect` without/invalid nonce | Rejected (no redirect, no state transient); subscriber gets 403 on admin page                                   | PHPUnit covers; curl as logged-in admin for the manual variant                                     |

## WP-BC-004 — Landing page status polling

| ID        | Case                                                              | Oracle / expected                                                          | Setup / notes                          |
| --------- | ----------------------------------------------------------------- | -------------------------------------------------------------------------- | --------------------------------------- |
| WP-TC-006 | Landing tab polls and reloads once connected                     | `admin-ajax.php?action=omnisend_connection_status` every ~2 s; auto-reload to Connected within ~5 s of consent completing in another tab | Two browser tabs                       |
| WP-TC-007 | `omnisend_connection_status` auth checks                          | No nonce → 403/`-1`; subscriber → 403; admin + nonce → `{"connected":bool}` | curl with WP cookies                   |

## WP-BC-005 — OAuth state lifetime

| ID        | Case                                              | Oracle / expected                                                        | Setup / notes                        |
| --------- | ------------------------------------------------- | ------------------------------------------------------------------------ | ------------------------------------- |
| WP-TC-008 | Finish consent >15 min (<1 h) after starting     | Callback accepted; tampered/expired/missing/replayed state rejected      | Manipulate transient or wait; state transient is consumed after use |

## WP-BC-006 — Actionable connection errors

| ID        | Case                                                                       | Oracle / expected                                                  | Setup / notes                              |
| --------- | -------------------------------------------------------------------------- | ------------------------------------------------------------------ | ------------------------------------------- |
| WP-TC-009 | `GET /api/brands/current` returns 401/403/410/429/5xx/bad JSON/non-JSON/3xx/WP_Error | Each maps to a specific WP_Error message on the landing page; tokens cleared on failure | `ApiResponseTest` + `OAuthConnectionTest` (mock `wp_remote_*`); live: bad key → "HTTP error: 401" |
| WP-TC-010 | API-key connect where the key lacks `brands.read`                         | Error names the missing permission; no partial connected state     | Needs a reduced-scope testing API key — human-only until one exists |

## WP-BC-007 / WP-BC-008 — Legacy & Woo auto-connect

| ID        | Case                                                              | Oracle / expected                                                                                          | Setup / notes                                            |
| --------- | ----------------------------------------------------------------- | ---------------------------------------------------------------------------------------------------------- | --------------------------------------------------------- |
| WP-TC-011 | Seeded `omni_send_core_api_key`, no `auth_mode` → load admin + cron sync | `auth_mode=api_key`; requests carry `Omnisend-API-Key` + `Omnisend-Version: 2026-03-15`; connected, no re-connect prompt | `wp option update omni_send_core_api_key …`              |
| WP-TC-012 | Woo plugin connected → activate core plugin                        | `connect_with_omnisend_for_woo_plugin` reuses the Woo API key, stores brand id, connected; idempotent on 2nd call | Local WP with Woo plugin + its `omnisend_api_key` option  |

## WP-BC-009 — WP user → contact sync

| ID        | Case                                       | Oracle / expected                                                                                                            | Setup / notes                                              |
| --------- | ------------------------------------------ | ---------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------------------- |
| WP-TC-013 | Register WP user, run contact-sync cron   | Contact created on the brand with tag `wordpress` + `wordpress_roles`; `omni_send_core_last_sync` set; response `id` mapped     | `wp cron event run omni_send_cron_sync_contacts`; verify via `GET api.omnisend.work/v3/contacts?email=…`. Users marked `ERROR` are never retried (pre-existing) — delete the meta to retry |

## WP-BC-010 / WP-BC-011 / WP-BC-012 — SDK surface

| ID        | Case                                                        | Oracle / expected                                                                                  | Setup / notes                          |
| --------- | ----------------------------------------------------------- | -------------------------------------------------------------------------------------------------- | --------------------------------------- |
| WP-TC-014 | `get_contact_by_email` existing + missing                  | Existing → Contact with `id`; missing → `get_wp_error()` "Contact not found." (`$contact` nullable) | PHPUnit `ClientTest` + `wp shell`      |
| WP-TC-015 | `send_customer_event` full payload                          | `POST /api/events` 2xx; event visible on the brand with mapped properties                          | `ClientTest` + one real testing call   |
| WP-TC-016 | Product/category/batch CRUD + 255-char category boundary   | 255 accepted, 256 rejected; `/api/products`, `/api/product-categories`, `/api/batches` all 2xx      | `ProductTest`/`CategoryTest`/`ClientTest` + real calls |

## WP-BC-013 — Token refresh

| ID        | Case                                                          | Oracle / expected                                                                          | Setup / notes                                                             |
| --------- | ------------------------------------------------------------- | ------------------------------------------------------------------------------------------ | -------------------------------------------------------------------------- |
| WP-TC-017 | Force `omni_send_core_oauth_token_expires_at` to the past, trigger any API call | `POST /oauth2/token` `grant_type=refresh_token`; new access token, same refresh token, `expires_at` ~now+30d; call within validity does not refresh; refresh failure → tokens cleared | `wp option update …_token_expires_at 1`, then e.g. a WP user login sync   |

## WP-BC-014 — Landing page rendering

| ID        | Case                                                    | Oracle / expected                                                                    | Setup / notes                    |
| --------- | ------------------------------------------------------- | ------------------------------------------------------------------------------------ | --------------------------------- |
| WP-TC-018 | Landing page before connect, plain + Hostinger flag    | "Create new account" + "Connect your account" (`target=_blank`); Hostinger keeps "Explore Omnisend"; no API-key form | Browser; Hostinger via stub plugin |

## WP-BC-015 — Disconnect / uninstall / failed callback

| ID        | Case                                                              | Oracle / expected                                                                          | Setup / notes                                   |
| --------- | ----------------------------------------------------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------ |
| WP-TC-019 | Disconnect from Connected page; then uninstall                   | Disconnect removes `omni_send_core_*` except DCR client id/secret + clears sync meta; uninstall wipes everything; landing page shown again | `wp option list --search='omni_send_core_*'` → empty after uninstall |
| WP-TC-029 | Callback with `?error=access_denied` / bad state                | Error notice ("Omnisend did not authorize this store: access_denied."); oauth tokens + `auth_mode` cleared; Connect retriable | Craft callback URL on a connected-flow install   |

## WP-BC-016 / WP-BC-017 — Compatibility surface

| ID        | Case                                                                  | Oracle / expected                                                        | Setup / notes                     |
| --------- | --------------------------------------------------------------------- | ------------------------------------------------------------------------ | ---------------------------------- |
| WP-TC-020 | Full PHPUnit suite on change head + public-signature diff vs `main`  | All green; only intentional changes (e.g. `GetContactResponse::$contact` nullable) | `./vendor/bin/phpunit tests`      |
| WP-TC-021 | Retained `/v3` calls (`connect_store` existing brand, orders)        | Still `X-API-Key` headers against `OMNISEND_CORE_API_V3`; no `Omnisend-Version` | Code review + PHPUnit             |

## WP-BC-018 — Build / lint pipeline

| ID        | Case                                                   | Oracle / expected | Setup / notes                                        |
| --------- | ------------------------------------------------------ | ----------------- | ----------------------------------------------------- |
| WP-TC-022 | `npm run build`, `lint:js`, `phpcs`, `phpunit`, `zip_plugin.sh` on change head | All pass          | `lint:js` has a pre-existing error baseline on `main` — compare, don't expect 0 |

## WP-BC-019 — Email lifecycle events (#164, pending)

| ID        | Case                                                                                | Oracle / expected                                                          | Setup / notes                  |
| --------- | ----------------------------------------------------------------------------------- | -------------------------------------------------------------------------- | ------------------------------- |
| WP-TC-023 | Register user / password change / email change with opt-in enabled and disabled    | Events sent to `/events` only when `omni_send_core_email_service_opt_in` enabled; option deleted on disconnect | Requires the email-service feature (PR #164) to be merged; toggle the option via `wp option` |

## WP-BC-020 — OAuth client re-registration

| ID        | Case                                                                                 | Oracle / expected                                                                                       | Setup / notes                                                        |
| --------- | ------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------- | --------------------------------------------------------------------- |
| WP-TC-024 | Consent abandoned (no token), then `siteurl`/`home` changed → Connect again        | New client registered with the new redirect URI; consent completes; a store with a valid token does NOT re-register | `wp option update siteurl/home`; issuer rejects `http://` redirect URIs for non-localhost hosts |
| WP-TC-025 | Registration failure (issuer 4xx/5xx) on re-register                               | Previous client kept; actionable error surfaced                                                        | `OAuthClientRegistrationTest`                                       |

## WP-BC-021 — Contact write merge tags / PATCH by id

| ID        | Case                                                                            | Oracle / expected                                                              | Setup / notes                                   |
| --------- | ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------ | ------------------------------------------------ |
| WP-TC-026 | `create_contact [a]` → `save_contact` by email `[b]` → `save_contact` by id `[c]` | Contact ends `[a,b,c]`; by-id write uses `PATCH /api/contacts/{id}`; no-tags write skips the lookup GET | Real calls to a testing brand via the plugin SDK |
| WP-TC-027 | `save_contact` when the lookup GET fails / contact unknown                     | Own tags sent as-is; write still succeeds; unknown email → "Contact not found." | `ClientTest`                                     |

## WP-BC-022 — API-key form removed

| ID        | Case                                                        | Oracle / expected                                          | Setup / notes        |
| --------- | ----------------------------------------------------------- | ---------------------------------------------------------- | --------------------- |
| WP-TC-028 | Fresh install: landing page + `POST omnisend/v1/connect`   | No API-key input; REST route 404; only the OAuth buttons   | Browser + curl        |

## WP-BC-023 — Connected-state contract

| ID        | Case                                        | Oracle / expected                                            | Setup / notes                                                            |
| --------- | ------------------------------------------- | ------------------------------------------------------------ | ------------------------------------------------------------------------ |
| WP-TC-030 | Disconnected plugin sends nothing          | SDK calls no-op / error cleanly when `is_connected()` false | Guard conditions: `is_plugin_active` + `class_exists` + `is_connected()` |

## WP-BC-024 — Tracking snippet

| ID        | Case                              | Oracle / expected                                   | Setup / notes                 |
| --------- | --------------------------------- | --------------------------------------------------- | ------------------------------ |
| WP-TC-031 | Snippet renders when connected   | `launcher-v2.js` tag in page footer on public pages | View source / DOM check       |
| WP-TC-032 | No snippet when disconnected     | No snippet markup after disconnect                  | Contrast with WP-TC-031       |

## WP-BC-025 — Compatibility headers

| ID        | Case                                                       | Oracle / expected                                                                    | Setup / notes                |
| --------- | ---------------------------------------------------------- | ------------------------------------------------------------------------------------ | ----------------------------- |
| WP-TC-033 | `omnisend/readme.txt` headers vs verified support matrix  | `Requires PHP` / `Tested up to` match the WP × PHP versions the plugin was actually tested against | Manual check on version bumps |