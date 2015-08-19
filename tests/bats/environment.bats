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

@test "phpcs binary available in current session" {
  command -v phpcs
}

@test "phpcbf binary present" {
  ls vendor/bin/phpcbf
}

@test "phpcbf binary available in current session" {
  command -v phpcbf
}

@test "Drupal package present" {
  ls vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml
}

@test "phpunit is available in current session" {
  command -v phpunit
}

@test "phpunit version is the same as specified in composer config" {
  run ./vendor/phpunit/phpunit/phpunit --version
  [ "${lines[0]}" = "PHPUnit 4.6.6 by Sebastian Bergmann and contributors." ]
}

@test "dcr command available in current session" {
  command -v ./dcr
}
