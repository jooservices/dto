# Ignore Files Documentation

This document describes all ignore files in the project and their purposes.

## Files Overview

### `.gitignore`
**Purpose:** Tells Git which files and directories to ignore when tracking changes.

**Key Sections:**
- Composer dependencies (`/vendor/`, `composer.lock`)
- IDE files (`.idea/`, `.vscode/`, etc.)
- PHPUnit cache and coverage reports
- Code quality tool caches (PHP-CS-Fixer, PHPStan, PHPMD)
- Build artifacts (`/build/`, `/dist/`)
- Log files
- Environment files (`.env*`)
- OS-specific files (`.DS_Store`, `Thumbs.db`)
- Temporary files
- TypeScript generation outputs (if implemented)

### `.cursorignore`
**Purpose:** Tells Cursor AI which files to ignore when analyzing the codebase.

**Key Sections:**
- Dependencies (vendor, node_modules)
- Build and cache directories
- Generated files (TypeScript definitions, generated PHP)
- Test output and coverage
- IDE and editor files
- OS files
- Logs
- Environment files
- Large binary files
- CI/CD configuration files

**Note:** This helps Cursor AI focus on actual source code and avoid processing large generated files or dependencies.

### `.gitattributes`
**Purpose:** Controls Git behavior for line endings, binary file handling, and export filters.

**Key Features:**
- **Line Endings:** Ensures consistent LF line endings for all text files
- **Binary Files:** Marks image, font, and archive files as binary
- **Export Filters:** Excludes development files from Composer package exports
- **Merge Strategy:** Sets `composer.lock` to use union merge strategy

**Benefits:**
- Prevents line ending issues across different operating systems
- Ensures clean package exports (only production files)
- Proper handling of binary files

### `.editorconfig`
**Purpose:** Provides consistent coding style across different editors and IDEs.

**Key Settings:**
- UTF-8 encoding
- LF line endings
- 4 spaces for PHP files
- 2 spaces for JSON, YAML, XML
- Trim trailing whitespace
- Insert final newline

**Supported Editors:**
- VS Code (with extension)
- PhpStorm/IntelliJ IDEA
- Sublime Text
- Atom
- Vim
- And many more...

### `.dockerignore`
**Purpose:** Excludes files from Docker build context to reduce image size and build time.

**Key Sections:**
- Git files (not needed in container)
- Dependencies (installed via Composer in container)
- IDE files
- Build artifacts
- Test output
- Documentation (except README)
- Environment files (use Docker secrets instead)
- CI/CD files

**Benefits:**
- Faster Docker builds
- Smaller build context
- More secure (excludes sensitive files)

## Usage

### For Developers

1. **`.gitignore`** - Automatically used by Git. No action needed.
2. **`.editorconfig`** - Install EditorConfig extension in your IDE for automatic formatting.
3. **`.gitattributes`** - Automatically used by Git. Run `git add --renormalize .` if you have existing files with wrong line endings.
4. **`.cursorignore`** - Automatically used by Cursor AI. No action needed.
5. **`.dockerignore`** - Automatically used by Docker. No action needed.

### For CI/CD

All ignore files are automatically respected by their respective tools:
- Git operations use `.gitignore` and `.gitattributes`
- Docker builds use `.dockerignore`
- Cursor AI uses `.cursorignore`

## Maintenance

### When to Update

- **`.gitignore`**: When adding new tools or build processes
- **`.cursorignore`**: When adding new generated file types
- **`.gitattributes`**: When adding new file types to the project
- **`.editorconfig`**: When changing coding style standards
- **`.dockerignore`**: When changing Docker build process

### Best Practices

1. Keep ignore files organized with clear sections
2. Add comments explaining why files are ignored
3. Review ignore files periodically to ensure they're still relevant
4. Don't ignore files that should be tracked (like configuration templates)

## Common Patterns

### PHP Projects
- `/vendor/` - Composer dependencies
- `composer.lock` - Lock file (usually ignored in libraries)
- `.phpunit.cache/` - PHPUnit cache
- `coverage/` - Test coverage reports

### Code Quality Tools
- `.php-cs-fixer.cache` - PHP-CS-Fixer cache
- `.phpstan/` - PHPStan cache
- `phpmd.log` - PHPMD output

### IDEs
- `.idea/` - PhpStorm/IntelliJ IDEA
- `.vscode/` - VS Code
- `*.sublime-*` - Sublime Text

### OS Files
- `.DS_Store` - macOS
- `Thumbs.db` - Windows
- `desktop.ini` - Windows

## Troubleshooting

### Line Ending Issues

If you see line ending warnings or issues:

```bash
# Normalize all line endings
git add --renormalize .
git commit -m "Normalize line endings"
```

### Files Still Being Tracked

If files are still being tracked after adding to `.gitignore`:

```bash
# Remove from Git cache (but keep local files)
git rm --cached <file>
git commit -m "Stop tracking <file>"
```

### Docker Build Context Too Large

If Docker builds are slow or context is large:

1. Check `.dockerignore` includes all unnecessary files
2. Verify large files/directories are listed
3. Use `.dockerignore` patterns effectively

## References

- [Git Ignore Documentation](https://git-scm.com/docs/gitignore)
- [Git Attributes Documentation](https://git-scm.com/docs/gitattributes)
- [EditorConfig Documentation](https://editorconfig.org/)
- [Docker Ignore Documentation](https://docs.docker.com/engine/reference/builder/#dockerignore-file)
- [Cursor AI Documentation](https://cursor.sh/docs)
