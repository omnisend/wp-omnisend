---
owner: ecom-platforms
last-verified: 2026-09-11
---

# Business cases — wp-omnisend (core WordPress plugin)

Intent + acceptance criteria per case. `WP-BC-xxx` IDs are stable and never
reused; specs here and in polar-bear reference them in test titles. Add new cases
at the end.

| ID        | Business case                                                                                | Acceptance criteria                                                                                                                                      | Coverage                                        |
| --------- | -------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------- | ----------------------------------------------- |
| WP-BC-001 | Merchant connects a WordPress site to Omnisend (OAuth consent → token exchange)              | Connect flow completes end to end; disconnect cleanly resets state; connected status is queryable                                                          | `tests/` (unit), polar-bear `@ecom-*` connected specs |
| WP-BC-002 | Connected state is a reliable contract for integrating plugins                               | `Omnisend::is_connected()` and option state reflect reality; third-party plugins can't send data while disconnected/not installed                             | `tests/`                                        |
| WP-BC-003 | SDK contact save/update — `get_client(name, version)` → create/update contact with consent   | Email/phone/custom fields + consent round-trip to Omnisend API; failures surfaced as typed responses                                                         | `tests/`                                        |
| WP-BC-004 | SDK catalog + events — products (incl. variants), categories, customer events, batch sends   | Create/update/delete honored; batch endpoint aggregates correctly; `integration name/version` tagging preserved                                              | `tests/`                                        |
| WP-BC-005 | Front-end tracking snippet — visitor tracking snippet injected on storefront pages           | Snippet renders on public pages when connected, absent when disconnected                                                                                     | —                                               |
| WP-BC-006 | Compatibility headers stay honest — `Requires PHP` / `Tested up to` reflect verified support | wordpress.org install warnings match what was actually tested (see wp-tested-up-to-bump workflow)                                                             | —                                               |

_Gaps (—) are candidates for PHPUnit coverage here or polar-bear specs downstream._
