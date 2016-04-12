#!/usr/bin/env bats
#
# Application tests for DCR.
#

load test_helper

setup() {
  if [ -z "$DCR_PROJECT_ROOT" ]; then
    DCR_PROJECT_ROOT="../"
  fi

  source ~/.profile
  DCR_FIXTURES_DIR=$DCR_PROJECT_ROOT/tests/standards_fixtures
}

@test "Custom vendor standards are picked up" {
  run dcr -i
  assert_equal "The installed coding standards are MySource, PEAR, PHPCS, PSR1, PSR2, Squiz, Zend, Drupal, DrupalPractice, DCR and App" "$output"
}

@test "Single valid review passes" {
  run dcr $DCR_FIXTURES_DIR/hooks.valid.php
  assert_success
}

@test "Multiple valid reviews passes" {
  run dcr $DCR_FIXTURES_DIR/hooks.valid.php $DCR_FIXTURES_DIR/hooks.valid2.php
  assert_success
}

@test "Single invalid review fails" {
  run dcr $DCR_FIXTURES_DIR/hooks.invalid.php
  assert_failure
}

@test "Multiple invalid review fails" {
  run dcr $DCR_FIXTURES_DIR/hooks.invalid.php $DCR_FIXTURES_DIR/hooks.invalid2.php
  assert_failure
}

@test "Multiple valid and invalid review fails" {
  run dcr $DCR_FIXTURES_DIR/hooks.valid.php $DCR_FIXTURES_DIR/hooks.invalid.php
  assert_failure
}

@test "Dir with valid items pass" {
  run dcr $DCR_FIXTURES_DIR/valid
  assert_success
}

@test "Dir with invalid items fail" {
  run dcr $DCR_FIXTURES_DIR/invalid
  assert_failure
}

@test "Dir with valid and invalid items fail" {
  run dcr $DCR_FIXTURES_DIR
  assert_failure
}

@test "Parameter --help works" {
  run dcr --help
  assert_success

  assert_contains "Drupal Code Review" "${lines[0]}"
  assert_contains "Lint:" "$output"
  assert_contains "Fix:" "$output"
}

@test "Parameter --explain works" {
  run dcr --explain $DCR_FIXTURES_DIR/hooks.invalid.php
  assert_failure

  assert_contains "(Drupal.Commenting.FunctionComment.WrongStyle)" "${lines[5]}"
}

@test "Parameter passthrough works" {
  run dcr --standard=PSR1 $DCR_FIXTURES_DIR/hooks.invalid.php
  assert_success
}

@test "Fixing code works" {
  cp $DCR_FIXTURES_DIR/hooks.invalid.php $DCR_FIXTURES_DIR/hooks.invalid.tmp.php

  # Run the code fixing.
  run bash -c "dcr fix $DCR_FIXTURES_DIR/hooks.invalid.tmp.php"

  run diff $DCR_FIXTURES_DIR/hooks.invalid.php $DCR_FIXTURES_DIR/hooks.invalid.tmp.php
  assert_failure

  rm $DCR_FIXTURES_DIR/hooks.invalid.tmp.php
}
