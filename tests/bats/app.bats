#!/usr/bin/env bats
#
# Integration tests for DCR.
#
# Tests to make sure that dcr command bootstraps properly.
#

load test_helper

@test "Bootstrap to help" {
  run "dcr --help"

  [ "$status" -eq 0 ]
  [ "${lines[0]}" = "Usage:" ]
}

@test "Valid file" {
  run "dcr tests/standards_fixtures/hooks.valid.php"

  [ "$status" -eq 0 ]
}

@test "Invalid file" {
  run "dcr tests/standards_fixtures/hooks.invalid.php"

  [ "$status" -eq 1 ]
}

@test "Directory" {
  run "dcr tests/standards_fixtures"

  [ "$status" -eq 1 ]
}
