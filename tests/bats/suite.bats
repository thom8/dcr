#!/usr/bin/env bats

@test "phpcs binary present" {
  ls vendor/bin/phpcs
}

@test "phpcbf binary present" {
  ls vendor/bin/phpcbf
}

@test "Drupal package present" {
  ls vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml
}

@test "DrupalPractice package present" {
  ls vendor/drupal/coder/coder_sniffer/DrupalPractice/ruleset.xml
}

@test "DrupalSecure package present" {
  ls vendor/coltrane/DrupalSecure/DrupalSecure/ruleset.xml
}

@test "jslint.js present" {
  ls vendor/douglascrockford/JSLint/jslint.js
}

@test "Rhino js.jar present" {
  ls vendor/mozilla/rhino/js.jar
}

@test "ultimatejslint.js created" {
  ls jslint/ultimatejslint.js
}

@test "phpunit is available in current session" {
  command -v phpunit
}

@test "phpunit version is the same as specified in composer" {
  run phpunit --version
}

@test "dcr command available in current session" {
  command -v ./dcr
}
