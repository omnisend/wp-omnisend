---
owner: ecom-platforms
last-verified: 2026-09-09
---

# QA context

Canonical store for this plugin's testing context — the business cases it owes
merchants and integrating plugins, plus anything about testing it that code can't
express. Read by humans and coding agents (Devin/Cursor via `AGENTS.md`, Claude
Code via `CLAUDE.md` → `@AGENTS.md`).

## Layout

| Path                       | Contents                                                             |
| -------------------------- | -------------------------------------------------------------------- |
| `business-cases/<area>.md` | Stable `WP-BC-xxx` IDs — intent + acceptance criteria, not steps     |
| `test-cases/<area>.md`     | Only what code can't express: test oracle, data setup, manual checks |

## Rules

- **Business cases live in the service/plugin repo** — here. They describe what
  this plugin promises; they change in the same PR as the behavior change.
- **IDs are namespaced and stable:** `WP-BC-001`, never reused. Tests here and
  polar-bear specs reference them in titles (`test('WP-BC-003 …', …)`); coverage
  maps are derived by grepping for IDs — never maintain a manual index.
- **Test case = code.** Markdown holds intent, oracles, and data-setup rules only
  — never restate steps a test already encodes.
- **Secrets:** reference the secret _name_ (env var, GSM secret id), never a value.
- **Freshness:** every file carries `owner:` and `last-verified:` headers. QA gap
  reports flag stale files; fixes land as PRs, not session artifacts.
- **Durable only:** files hold case + oracle + setup. Per-run results
  (PASS/FAIL/flaky) stay in QA reports — never commit run history.

## QA Orchestrator contract

`docs/qa/` is the input and output of QA runs:

- **Before planning** — the orchestrator reads `business-cases/` +
  `test-cases/` first and reuses `WP-BC-*` / `WP-TC-*` IDs in its impact matrix
  and test plan instead of regenerating cases. Consistent IDs across runs make
  coverage tracking work.
- **After reporting** — newly discovered cases land as a PR appending rows to
  these files; stale cases get flagged. Nothing about a run result is committed.

## Neighboring context (does NOT live here)

- Test-runner context (env topology, shared test data, flakes) → `docs/qa/` in
  `omnisend/polar-bear`.
- Downstream plugin cases (WooCommerce sync etc.) → `omnisend-connect`'s own repo.
- Human review tracking → Jira/Confluence; link, don't mirror.
