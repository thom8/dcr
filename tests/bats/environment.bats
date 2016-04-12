#!/usr/bin/env bats
#
# Environment tests for DCR.
#
# Tests to make sure that environment has all required dependencies and is ready
# to be used for DCR.
#
setup() {
  if [ -z "$DCR_PROJECT_ROOT" ]; then
    PROJECT_ROOT="../"
  fi
}

@test "phpcs binary present" {
  ls $DCR_PROJECT_ROOT/vendor/bin/phpcs
}

@test "phpcs binary available in current session" {
  command -v phpcs
}

@test "phpcbf binary present" {
  ls $DCR_PROJECT_ROOT/vendor/bin/phpcbf
}

@test "phpcbf binary available in current session" {
  command -v phpcbf
}

@test "Drupal package present" {
  ls $DCR_PROJECT_ROOT/vendor/drupal/coder/coder_sniffer/Drupal/ruleset.xml
}

@test "dcr command available in current session" {
  command -v dcr
}
