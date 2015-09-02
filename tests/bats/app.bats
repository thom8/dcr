#!/usr/bin/env bats
#
# App tests for DCR.
#

load test_helper

setup() {
  source ~/.profile
  fixtures_dir=tests/standards_fixtures
}

@test "Custom vendor standards are picked up" {
  run dcr -i
  assert_equal "The installed coding standards are MySource, PEAR, PHPCS, PSR1, PSR2, Squiz, Zend, Drupal, DrupalPractice, DCR and App" "$output"
}

@test "Single valid review passes" {
  run dcr $fixtures_dir/hooks.valid.php
  assert_success
}

@test "Multiple valid reviews passes" {
  run dcr $fixtures_dir/hooks.valid.php $fixtures_dir/hooks.valid2.php
  assert_success
}

@test "Single invalid review fails" {
  run dcr $fixtures_dir/hooks.invalid.php
  assert_failure
}

@test "Multiple invalid review fails" {
  run dcr $fixtures_dir/hooks.invalid.php $fixtures_dir/hooks.invalid2.php
  assert_failure
}

@test "Multiple valid and invalid review fails" {
  run dcr $fixtures_dir/hooks.valid.php $fixtures_dir/hooks.invalid.php
  assert_failure
}

@test "Dir with valid items pass" {
  run dcr $fixtures_dir/valid
  assert_success
}

@test "Dir with invalid items fail" {
  run dcr $fixtures_dir/invalid
  assert_failure
}

@test "Dir with valid and invalid items fail" {
  run dcr $fixtures_dir
  assert_failure
}

@test "Parameter --help works" {
  run dcr --help
  assert_success

  assert_contains "Drupal Code Review" "${lines[0]}"
  assert_contains "Usage:" "$output"
}

@test "Parameter --explain works" {
  run dcr --explain $fixtures_dir/hooks.invalid.php
  assert_failure

  assert_contains "(Drupal.Commenting.FunctionComment.WrongStyle)" "${lines[5]}"
}

@test "Parameter passthrough works" {
  run dcr --standard=PSR1 $fixtures_dir/hooks.invalid.php
  assert_success
}

@test "Fixing code works" {
  cp $fixtures_dir/hooks.invalid.php $fixtures_dir/hooks.invalid.tmp.php

  # Run the code fixing.
  run bash -c "dcr fix $fixtures_dir/hooks.invalid.tmp.php"

  run diff $fixtures_dir/hooks.invalid.php $fixtures_dir/hooks.invalid.tmp.php
  assert_failure

  rm $fixtures_dir/hooks.invalid.tmp.php
}
