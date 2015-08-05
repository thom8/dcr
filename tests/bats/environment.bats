#!/usr/bin/env bats
#
# Environment tests for DCR.
#
# Tests to make sure that environment has all required dependencies and is ready
# to be used for DCR.
#

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
  command -v ./vendor/phpunit/phpunit/phpunit
}

@test "phpunit version is the same as specified in composer config" {
  run ./vendor/phpunit/phpunit/phpunit --version
  [ "${lines[0]}" = "PHPUnit 4.6.6 by Sebastian Bergmann and contributors." ]
}

@test "dcr command available in current session" {
  command -v ./dcr
}
