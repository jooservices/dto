# GitHub Actions workflow flow

This document describes the workflows currently defined in
`.github/workflows/`. All jobs run on the self-hosted `runner1` Linux X64
runner. PHP-related commands run through the repository Docker Compose setup.

## Overall event flow

```mermaid
flowchart TD
    native[GitHub Secret Scanning and Push Protection] --> Alerts[GitHub security alerts or blocked push]

    push[Push to master or develop] --> CI[CI]
    push --> CodeQL[CodeQL]
    push --> Drafter[Release Drafter]
    push --> Audit{Changed files under .github?}
    Audit -->|yes| WorkflowAudit[Workflow audit]

    pr[PR opened / edited / synchronized / reopened] --> CI
    pr --> CodeQL
    pr --> Commitlint[Commitlint]
    pr --> Semantic[Semantic PR Title]
    pr --> PathLabel[PR Labeler]
    pr --> SizeLabel[PR size labeler]
    pr --> Audit

    first[First issue or PR] --> Welcome[First interaction]

    tag[Push tag v*.*.*] --> Release[Release]

    weekly[Weekly schedules] --> CodeQL
    weekly --> LinkCheck[Link check]
    weekly --> Scorecard[OpenSSF Scorecard]
    weekly --> WorkflowAudit

    daily[Daily schedule] --> Stale[Stale]

    manual[workflow_dispatch] --> LinkCheck
    manual --> Scorecard
    manual --> Stale
    manual --> WorkflowAudit
```

## Pull request flow

```mermaid
flowchart TD
    PR[PR activity] --> CI[CI quality gate]
    PR --> Native[GitHub Secret Scanning]
    PR --> CL[Validate commit messages]
    PR --> SPT[Validate PR title]
    PR --> PL[Apply path labels]
    PR --> PSL[Apply size label]
    PR --> CQL[Analyze GitHub Actions with CodeQL]
    PR --> WA{Workflow files changed?}
    WA -->|yes| AL[Actionlint]
    WA -->|yes| ZM[Zizmor]

    CI --> V[Validate]
    V --> L[Lint]
    L --> T[Test and coverage]
    T --> S[Security scans]
    S --> CU[Merge reports and upload to Codecov]
```

The CI and PR-policy workflows are independent: a label or title check does
not wait for CI, and CI does not wait for those checks.

## CI (`ci.yml`)

**Triggers:** push or pull request targeting `master` or `develop`.
Concurrent runs for the same Git ref cancel older in-progress runs.

```mermaid
flowchart LR
    V[Validate] --> L[Lint] --> T[Test] --> S[Security] --> C[Coverage upload]

    V --- V1[Checkout, prepare runner, build PHP image]
    V --- V2[Restore/install Composer dependencies]
    V --- V3[composer validate --strict]

    L --- L1[Run composer lint]
    T --- T1[Unit suite + Clover report]
    T --- T2[Integration suite + Clover report]
    T --- T3[Enforce 85% coverage floor per suite]
    T --- T4[Upload coverage-clover artifact]
    S --- S1[Composer audit]
    S --- S2[OSV Scanner]
    S --- S3[Gitleaks OSS CLI in pinned Docker image]
    S --- S4[Semgrep OSS]
    S --- S5[Dependency Review on PRs only]
    C --- C1[Download coverage artifact]
    C --- C2[Merge Unit and Integration Clover reports]
    C --- C3[Upload to Codecov]
```

Each CI job repairs prior Docker-owned workspace files, checks out the source,
prepares the self-hosted Docker environment, builds the PHP image, and restores
or installs Composer dependencies as needed. `security` and `coverage` check
out full Git history.

## Release flow (`release.yml`)

**Trigger:** push of a tag matching `v*.*.*`. Runs are not cancelled.

```mermaid
flowchart TD
    Tag[Push v*.*.* tag] --> Checkout[Checkout full history]
    Checkout --> Master{Tag commit is reachable from origin/master?}
    Master -->|no| Stop[Fail release]
    Master -->|yes| Setup[Prepare runner, build PHP image, install dependencies]
    Setup --> Quality[Composer validate, lint, PHPUnit coverage]
    Quality --> Trivy[Scan filesystem and PHP Docker image with Trivy]
    Trivy --> SARIF[Upload filesystem SARIF]
    SARIF --> SBOM[Generate SPDX JSON SBOM]
    SBOM --> GHRelease[Create GitHub Release with generated notes and SBOM]
    GHRelease --> Packagist[Notify Packagist]
```

The workflow fails if the tag is not on `origin/master`, or if Packagist
credentials are unavailable. It is therefore the publication path; do not tag
until the owner approves the `v3.0.0` release.

## Other workflows

| Workflow | Trigger | Flow / result |
| --- | --- | --- |
| `codeql.yml` | Push/PR on `master` or `develop`; Monday 06:00 UTC | Checkout → initialize CodeQL for GitHub Actions only → analyze and publish security results. |
| `commitlint.yml` | PR opened, edited, synchronized, reopened | Checkout full history → validate every PR commit against `.github/commitlint.config.mjs`. |
| `semantic-pr.yml` | PR opened, edited, synchronized | Validate PR title type and require an uppercase first subject character. |
| `pr-labeler.yml` | PR opened, synchronized, reopened | Checkout → apply labels from `.github/labeler.yml` based on changed paths. |
| `pr-size-labeler.yml` | PR opened, synchronized, reopened | Checkout → apply `size/XS` through `size/XXL` based on changed-line thresholds. |
| `first-interaction.yml` | First issue or PR opened | Post contributor welcome message and contribution/security guidance. |
| `release-drafter.yml` | Push to `develop` or `master` | Checkout → update draft release notes using `.github/release-drafter.yml`. |
| `link-check.yml` | Monday 04:00 UTC; manual | Checkout → Lychee checks Markdown links, excluding `vendor`, Packagist, Codecov, and mail links. |
| `scorecard.yml` | Push to `master`; Monday 00:00 UTC; manual | Checkout full history → OpenSSF Scorecard → upload SARIF. |
| `stale.yml` | Daily 01:00 UTC; manual | Mark issues/PRs stale after 60 inactive days; close 14 days later, except pinned/security/dependencies. |
| `workflow-audit.yml` | `.github/**` changes on push/PR; Monday 03:00 UTC; manual | Runs independent jobs: Actionlint checks workflow syntax and Zizmor scans workflow security, then uploads Zizmor SARIF when produced. |

## Scheduled maintenance timeline

All cron expressions use UTC, not the runner's local timezone.

```mermaid
gantt
    title Scheduled workflows (UTC)
    dateFormat  HH:mm
    axisFormat  %H:%M
    section Monday
    OpenSSF Scorecard      :milestone, 00:00, 0m
    Stale (also daily)     :milestone, 01:00, 0m
    Workflow audit         :milestone, 03:00, 0m
    Link check             :milestone, 04:00, 0m
    CodeQL                 :milestone, 06:00, 0m
```

## Shared runner preparation

Most workflows first repair ownership of `vendor/` and `build/`, which can be
left root-owned by Docker. The PHP CI and release workflows additionally use
the local composite action `.github/actions/self-hosted-prepare` before
building or running the PHP container.

## Notes

- All declared workflows use dedicated repository configuration; none use
  `jooservices/workflows`.
- Secret scanning has two layers: GitHub Secret Scanning and Push Protection
  detect or block supported secrets at GitHub, while CI scans the checked-out
  Git history with the MIT-licensed Gitleaks OSS CLI. GitHub Secret Scanning
  and Push Protection are enabled in the repository security settings; they
  are not controlled by a workflow file.
- CI sends coverage only to Codecov. The README currently mentions Sonar in
  its CI chain, but no workflow uploads coverage to Sonar.
- Release is tag-driven, but its `origin/master` ancestry gate prevents a tag
  from publishing a commit that is not on the production branch.
