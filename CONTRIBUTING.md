# Contributing

Thanks for helping improve Laravel Toolkit.

## Development setup

Fork and clone the repository, then install its PHP dependencies:

```bash
composer install
```

The package uses Orchestra Testbench, so a separate Laravel application is not required for the test suite.

## Before opening a pull request

Run the formatter and test suite:

```bash
composer format
composer test
```

Please keep each pull request focused, add or update tests for behavioral changes, and update the relevant guide when
the public API or setup process changes. Do not edit `CHANGELOG.md` for unreleased changes unless a maintainer asks you
to do so; release automation manages version entries.

## Reporting bugs

Use a GitHub issue for reproducible bugs and include:

- the Laravel Toolkit, Laravel, PHP, and Inertia versions;
- the smallest example that reproduces the behavior;
- the expected and actual results;
- the relevant exception and stack trace, with secrets removed.

Security issues must be reported privately through a
[GitHub security advisory](https://github.com/wsssoftware/laraveltoolkit/security/advisories/new).

---

[Back to the project README](README.md)
