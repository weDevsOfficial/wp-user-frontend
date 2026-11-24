---
name: grill-with-docs
description: Structured planning interview that resolves a fuzzy plan/design into repository docs. Asks targeted questions in rounds, lands agreed domain terms into CONTEXT.md the moment they resolve, and gates hard trade-off decisions into docs/adr/. Use when a change fits one session and the plan needs sharpening before spec/implementation.
---

# Grill with Docs

Adapted for WPUF from Matt Pocock's skill: https://github.com/mattpocock/skills/blob/main/docs/engineering/grill-with-docs.md

Conduct a **stateful** planning interview: progressively build shared understanding and document outcomes as repo files *as they resolve* — not batched at the end. The glossary is the point: the project's own words, agreed once.

## When to use

- A repository task with a fuzzy plan that needs clarification before building.
- The change fits within a single session. (Multi-session effort → use a longer-form wayfinding approach instead.)
- You want domain vocabulary and decision documentation built simultaneously.

## Hard rules

1. **Resolve code-answerable questions by inspection, never by asking.** Grep/read the codebase (or spawn `Explore` mappers) first. Only genuine product/trade-off calls reach the user.
2. **Land terms the moment they resolve.** Each agreed term is written to `CONTEXT.md` immediately, not at the end.
3. **The glossary holds vocabulary only** — the project's words and what they mean. No implementation detail; that belongs in the plan/spec.
4. **Gate ADRs strictly.** A decision becomes an ADR only if it is *all three*: hard to reverse, surprising without context, and involves a real trade-off. **Few or no ADRs is the expected, healthy outcome.**
5. **One round at a time.** Ask a focused batch, let the user reflect, integrate, then ask the next round.

## Procedure

1. **Pre-inspect.** Map the relevant code first (files, hooks, data model, existing conventions). In WPUF this means the usual anchors — the free/pro split, the CPT/option that stores config, the query builder, the REST/AJAX route, the existing UI components + `wpuf-` tokens. Write the resolved facts into `CONTEXT.md` as ✅ glossary entries before interviewing.
2. **Seed `CONTEXT.md`** in the working docs area (e.g. `docs/plans/<feature>/CONTEXT.md`) with: a status legend (✅ resolved / 🟡 pending / 📌 ADR), the resolved glossary, an "Open decisions" list, and an empty "Decisions log".
3. **Interview in rounds.** Use `AskUserQuestion` for each round — 2–4 targeted questions, each with the recommended option first and labelled `(Recommended)`. Ask only what code can't answer.
4. **Integrate immediately.** After each round: move answered items from 🟡 → ✅ in the glossary, append a dated entry to the Decisions log (decision + one-line rationale), and update any affected plan docs.
5. **Mint ADRs only when gated in** (rule 4). File under `docs/plans/<feature>/adr/NNNN-slug.md` with: Context, Decision, Consequences, Alternatives considered. Mark the term 📌 in `CONTEXT.md` and link it.
6. **Closure.** When the plan is sharp, hand off to spec/implementation. Summarize what resolved, list any ADRs, and confirm the build order.

## ADR template

```markdown
# NNNN — <decision title>

- Status: accepted
- Date: <YYYY-MM-DD>
- Deciders: <who>

## Context
<the forces / why this is hard to reverse and non-obvious>

## Decision
<the call, stated plainly>

## Consequences
<what this makes easy, what it makes hard, follow-ups>

## Alternatives considered
<each rejected option + the one-line reason>
```

## Success indicators

- `CONTEXT.md` grows incrementally *during* the session (not one commit at the end).
- Glossary is vocabulary-only; no implementation leakage.
- Few/zero ADRs — only the genuinely hard, non-obvious, trade-off decisions.
- Every question that code could answer was answered from code.

## WPUF notes

- Keep the free/pro boundary explicit in the glossary — a term like "pro preview (locked)" resolves against the existing convention (`wpuf_is_pro_active()`, `ProBadge`, `SingleSelect {isFree:false}`), not a new invention.
- Reuse existing components/tokens; if a decision would introduce a new UI element, that itself is an ADR-worthy trade-off.
- Text domains, hook prefixes, and the config store are code-answerable — resolve them, don't ask.
