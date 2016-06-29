# Drupal Code Review (DCR) [![Circle CI](https://circleci.com/gh/alexdesignworks/dcr.svg?style=shield)](https://circleci.com/gh/alexdesignworks/dcr)

### Bring all your code review dependencies with one command!

## Installation
There are 3 options:

### 1. Global installation
Install once, init for each project or run "as-is" to scan custom files.
```
composer global require alexdesignworks/dcr
dcr install && ~/.profile
cd your_project_dir
dcr init
```

Global build status: [![Circle CI](https://circleci.com/gh/alexdesignworks/dcr-global-demo.svg?style=shield)](https://circleci.com/gh/alexdesignworks/dcr-global-demo)

### 2. Local per-project installation
```
composer require alexdesignworks/dcr
vendor/bin/dcr install && ~/.profile
```

Local build status: [![Circle CI](https://circleci.com/gh/alexdesignworks/dcr-demo.svg?style=shield)](https://circleci.com/gh/alexdesignworks/dcr-demo)

### 3. Install as composer dependency for specific project:
In `composer.json`:
```json
{
  "minimum-stability": "dev",
  "require-dev": {
    "alexdesignworks/dcr": "0.1.*"
  },
  "scripts": {
    "post-update-cmd": [
      "bash vendor/bin/dcr install"
    ]
  }
}
```

## Usage
From previously initialised directory:
```
dcr
```

For custom code linting (provided that DCR was installed globally):
```
dcr file_or_directory
```

## FAQ
### What is it?
**DCR** (Drupal Code Review) is a command-line utility to check that produced code follows Drupal coding standards and best practices.

### More specifically
It is a shell script wrapper around PHP_CodeSniffer and JSCS with Drupal-related code sniffs. It uses native and custom 3rd party `phpcs` sniffs.

### Why should I use it?
Simply put - convenience. Run code review by only one command `dcr`.

### Why does it exist?
1. **Ease of install:** `composer require alexdesignworks/dcr`
2. **Less false-positives:** Drupal-specific exceptions allow to have clean DCR output.
3. **Simple call:** no need to call phpcs with tones of confusing parameters. Just use `dcr`.
4. **JS Linting:** no need to install standalone JS linter.
5. **Project-based configuration:** use `.dcr.yml` file to configure `dcr` for each project and make sure that your teammates are using exactly the same standards.

### Why is it a separate tool?
There was a need in a dead-simple solution to review code using a single command, but also be flexible enough to handle project-based sniff rule customisations and pluggable into a CI pipeline.

**DCR** installs as a single composer dev dependency either into your project or globally.

**DCR** contains the following:

* PHP_CodeSniffer with Drupal, Drupal Practice sniffs.
* Additional **DCR** sniff with more specific Drupal rules that.
* Custom project-related sniff - very handy for any custom inclusions.
* JavaScript code review using JSCS.
* Colour CLI output - easy to spot issues.

### Can it automatically review code on commit?
Yes. Use it in git pre-commit hook.

### Can it be used as a part of automated build?
Yes. In fact, there is a [dcr-demo](https://github.com/alexdesignworks/dcr-demo) and [dcr-global-demo](https://github.com/alexdesignworks/dcr-global-demo) projects were setup to test `dcr` integration.

## Does it automatically fix code?
Yes! If you run `dcr fix` it will try to fix code in all files using `phpcbf` with your current sniffs configuration.

### So, what about PHP_CodeSniffer and Drupal Coder module?
**DCR** is just a wrapper around PHP_CodeSniffer and Drupal Coder. It does not add more constrains on the code standards.

## Roadmap
* <del>Show success and fail status messages</del> DONE
* <del>Allow **DCR** sniffs</del> DONE
* <del>Allow custom sniffs</del> DONE
* <del>Automated fix</del> DONE `dcr fix`
* <del>Allow running only from project root dir or any subdirs</del> DONE
* <del>Add JS linting</del> DONE Using JSCS
* Limit files scan to N failed files
* Scan only files changed in current branch comparing to a `main` branch.

## License
GPL2
