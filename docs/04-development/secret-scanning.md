# 🔒 Secret Scanning Setup

This project implements **two-layer defense** to prevent committing secrets and credentials.

## 🛡️ Two-Layer Protection

```
┌─────────────────────────────────────────────────────┐
│              Layer 1: Local Git Hooks               │
│         (First line of defense - Immediate)         │
└──────────────────────┬──────────────────────────────┘
                       │
          ⚡ Blocks before commit/push
                       │
┌──────────────────────▼──────────────────────────────┐
│           Layer 2: GitHub Actions CI                │
│         (Safety net - Cannot be bypassed)           │
└─────────────────────────────────────────────────────┘
```

---

## 📋 What Gets Detected

Gitleaks scans for **1000+ secret patterns** including:

### Common Secrets:
- 🔑 API keys (AWS, Google, Stripe, etc.)
- 🔐 Private keys (SSH, GPG, RSA, etc.)
- 🎫 Access tokens (GitHub, GitLab, Slack, etc.)
- 🗝️ OAuth tokens and client secrets
- 🔒 Database connection strings with passwords
- 💳 Payment service credentials
- 📧 SMTP passwords
- 🌐 Generic API keys and secrets

### Patterns Detected:
```
aws_access_key_id = AKIAIOSFODNN7EXAMPLE
api_key = sk_live_51H...
password = "MySecretPass123"
mongodb://user:pass@host:27017
```

---

## 🎯 Layer 1: Local Git Hooks

### Pre-commit Hook
**What:** Scans staged files before commit  
**Speed:** ⚡ < 1 second  
**Coverage:** Changed files only

```bash
# Automatically runs on: git commit
🔒 Scanning for secrets...
# Scans only staged files (fast!)
```

### Pre-push Hook
**What:** Scans unpushed commits before push  
**Speed:** ⚡⚡ 1-5 seconds  
**Coverage:** All commits being pushed

```bash
# Automatically runs on: git push
🔒 Scanning commits for secrets...
# Scans commits that would be pushed
```

### Installation

Git hooks are **automatically installed** when you run:
```bash
composer install
# or
composer update
```

This is handled by CaptainHook via `post-install-cmd` and `post-update-cmd` scripts.

### Manual Installation (if needed)
```bash
vendor/bin/captainhook install --force
```

### Verify Hooks Are Installed
```bash
ls -la .git/hooks/
# Should see: pre-commit, pre-push
```

---

## 🚀 Layer 2: GitHub Actions CI

### Secret Scanning Workflow
**File:** `.github/workflows/secret-scanning.yml`

**Triggers:**
- ✅ Every push to `main` or `develop`
- ✅ Every pull request
- ✅ Weekly scheduled scan (Sundays at midnight UTC)
- ✅ Manual trigger via GitHub UI

**What it does:**
```yaml
- Checks out FULL git history
- Scans entire repository
- Blocks merge if secrets found
- Cannot be bypassed (required check)
```

### Security Job in Main CI
**File:** `.github/workflows/ci.yml`

Gitleaks runs as part of the main CI security checks:
```yaml
security:
  - Gitleaks Secret Scanning (full history)
  - Composer Audit (CVE check)
  - OpenSSF Scorecard (security best practices)
```

---

## ⚙️ Configuration

### .gitleaks.toml

The Gitleaks configuration file allows customization:

**Allowlisted files:**
- Markdown files (`.md`) - may contain example keys
- Test fixtures (`tests/fixtures/`)
- Vendor directory (`vendor/`)
- Lock files (`composer.lock`, `package-lock.json`)

**Allowlisted patterns:**
- `example_key` - Example keys in documentation
- `fake_secret` - Fake secrets in tests
- `test_password` - Test passwords
- `YOUR_API_KEY` - Placeholder text

**Example configuration:**
```toml
[allowlist]
paths = [
  '''\.md$''',
  '''^tests/fixtures/''',
]

regexes = [
  '''example[_-]?key''',
  '''fake[_-]?secret''',
]
```

---

## 🚫 What If a Secret Is Detected?

### Local (Pre-commit/Pre-push)

```bash
$ git commit -m "feat: Add feature"

🔒 Scanning for secrets...
⚠️  SECRET DETECTED!

    File: config.php
    Line: 23
    Type: Generic API Key
    
    23: $apiKey = "sk_live_51H...";
            ^^^^^^^^^^^^^^^^^^^^

❌ Commit blocked!
```

**What to do:**
1. **Remove the secret** from the code
2. **Move to environment variable** or config file
3. **Add to `.env`** (already in `.gitignore`)
4. **Use GitHub Secrets** for CI/CD
5. Try committing again

### CI/CD (GitHub Actions)

If a secret reaches CI (e.g., hook bypassed with `--no-verify`):

```
✅ Lint - PHPStan (passed)
✅ Lint - PHPMD (passed)
❌ Security Checks (failed)
   └─ Gitleaks - Secret Scanning (FAILED)
      Secret detected in commit abc123
      Cannot merge PR until fixed
```

**What to do:**
1. **DO NOT** merge the PR
2. Fix in a new commit (remove secret)
3. If secret already pushed:
   - Rotate/revoke the compromised credential
   - Consider `git rebase` to remove from history
   - Force push after cleanup

---

## 🔧 Local Usage

### Scan Staged Files
```bash
gitleaks protect --staged --verbose
```

### Scan All Uncommitted Changes
```bash
gitleaks protect --verbose
```

### Scan Entire Repository
```bash
gitleaks detect --verbose
```

### Scan Specific Commits
```bash
gitleaks detect --log-opts="HEAD~10..HEAD"
```

### Scan and Generate Report
```bash
gitleaks detect --report-path=gitleaks-report.json
```

---

## 🛠️ Installation Guide

### 1. Install Gitleaks

**macOS (Homebrew):**
```bash
brew install gitleaks
```

**Linux:**
```bash
# Debian/Ubuntu
sudo apt-get install gitleaks

# Or download binary
wget https://github.com/gitleaks/gitleaks/releases/download/v8.18.1/gitleaks_8.18.1_linux_x64.tar.gz
tar -xzf gitleaks_8.18.1_linux_x64.tar.gz
sudo mv gitleaks /usr/local/bin/
```

**Windows:**
```powershell
# Using Chocolatey
choco install gitleaks

# Or download from GitHub releases
```

**Docker:**
```bash
docker pull ghcr.io/gitleaks/gitleaks:latest
```

### 2. Verify Installation
```bash
gitleaks version
# Should output: v8.x.x
```

### 3. Install Git Hooks
```bash
composer install
# Hooks are automatically installed via CaptainHook
```

### 4. Test the Setup
```bash
# Try committing a test secret
echo 'api_key = "sk_live_test123456789"' > test.txt
git add test.txt
git commit -m "test: Check secret detection"

# Should be BLOCKED by Gitleaks!
# Clean up
git reset HEAD test.txt
rm test.txt
```

---

## 🚨 Bypassing Checks (NOT Recommended!)

### Local Hooks
```bash
# Bypass pre-commit hook (NOT RECOMMENDED!)
git commit --no-verify -m "message"

# Bypass pre-push hook (NOT RECOMMENDED!)
git push --no-verify
```

⚠️ **Warning:** Bypassed commits will still be caught by CI!

### CI Checks
**Cannot be bypassed** - CI checks are required and will block merges.

---

## 🎓 Best Practices

### 1. Never Commit Secrets
```bash
# ❌ BAD - Hardcoded secret
$apiKey = "sk_live_51H...";

# ✅ GOOD - Environment variable
$apiKey = getenv('API_KEY');

# ✅ GOOD - Config file (in .gitignore)
$apiKey = require __DIR__ . '/../.env.php';
```

### 2. Use .env Files
```bash
# .env (already in .gitignore)
API_KEY=your_secret_key_here
DB_PASSWORD=your_db_password
```

```php
// Load in code
$apiKey = $_ENV['API_KEY'] ?? getenv('API_KEY');
```

### 3. Use GitHub Secrets for CI
```yaml
# .github/workflows/ci.yml
env:
  API_KEY: ${{ secrets.API_KEY }}
```

### 4. Rotate Compromised Secrets
If a secret is accidentally committed:
1. **Immediately revoke/rotate** the credential
2. Clean git history (if not pushed)
3. Update to new credential in secure location

### 5. Review Before Commit
```bash
# Always review changes before committing
git diff

# Check what will be committed
git diff --cached
```

---

## 📊 Performance

| Layer | Scan Type | Files Scanned | Speed | Thoroughness |
|-------|-----------|---------------|-------|--------------|
| Pre-commit | Staged files | ~1-10 | ⚡ < 1s | Medium |
| Pre-push | Unpushed commits | ~10-100 | ⚡⚡ 1-5s | High |
| CI/CD | Full repository | All files | ⚡⚡⚡ 5-30s | Maximum |
| Weekly scan | Full history | All commits | ⚡⚡⚡ 10-60s | Complete |

---

## 🔍 Handling False Positives

### Option 1: Add to .gitleaks.toml
```toml
[allowlist]
regexes = [
  '''example_key_[a-zA-Z0-9]+''',
]
```

### Option 2: Use .gitleaksignore
```bash
# .gitleaksignore
path/to/file.php:line_number
README.md:*:generic-api-key
```

### Option 3: Inline Comment (use sparingly)
```php
// gitleaks:allow
$exampleKey = "not_a_real_key_for_documentation";
```

---

## 🐛 Troubleshooting

### Hooks Not Running
```bash
# Reinstall hooks
vendor/bin/captainhook install --force

# Check hook files exist
ls -la .git/hooks/pre-commit .git/hooks/pre-push
```

### Gitleaks Not Found
```bash
# Check if installed
which gitleaks

# Install if missing
brew install gitleaks  # macOS
```

### CI Failing
```bash
# Check workflow file syntax
cat .github/workflows/secret-scanning.yml

# View logs in GitHub Actions tab
```

### False Positives
```bash
# Add to allowlist in .gitleaks.toml
# See "Handling False Positives" section
```

---

## 📈 Weekly Scans

A scheduled workflow runs every Sunday to scan the entire git history:

```yaml
schedule:
  - cron: '0 0 * * 0'  # Every Sunday at midnight UTC
```

**Why?**
- Catches secrets in old commits
- Detects secrets in dependencies
- Ensures no secrets slipped through
- Security audit trail

---

## ✅ Verification Checklist

After setup, verify:

- [ ] Gitleaks installed locally (`gitleaks version`)
- [ ] Git hooks installed (`.git/hooks/pre-commit` exists)
- [ ] Test secret detection (try committing a fake secret)
- [ ] CI workflow exists (`.github/workflows/secret-scanning.yml`)
- [ ] Configuration file exists (`.gitleaks.toml`)
- [ ] Documentation reviewed (`SECRET_SCANNING.md`)

---

## 📚 Additional Resources

- **Gitleaks Documentation:** https://github.com/gitleaks/gitleaks
- **GitHub Secret Scanning:** https://docs.github.com/en/code-security/secret-scanning
- **CaptainHook Documentation:** https://github.com/captainhookphp/captainhook
- **OWASP Secrets Management:** https://cheatsheetseries.owasp.org/cheatsheets/Secrets_Management_Cheat_Sheet.html

---

## 🎉 Summary

Your repository now has **enterprise-grade secret protection**:

✅ **Layer 1:** Local git hooks (immediate feedback)  
✅ **Layer 2:** CI/CD checks (cannot be bypassed)  
✅ **Weekly scans:** Periodic full-history audits  
✅ **1000+ patterns:** Comprehensive secret detection  
✅ **Customizable:** Fine-tune with `.gitleaks.toml`  

**No secrets will slip through! 🔒**
