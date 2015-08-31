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
  assert_equal "The installed coding standards are MySource, PEAR, PHPCS, PSR1, PSR2, Squiz, Zend, Drupal and DrupalPractice" "$output"
}

@test "Single valid review passes" {
  run dcr $fixtures_dir/hooks.valid.php
  [ "$status" -eq 0 ]
}

@test "Multiple valid reviews passes" {
  run dcr $fixtures_dir/hooks.valid.php $fixtures_dir/hooks.valid2.php
  [ "$status" -eq 0 ]
}

@test "Single invalid review fails" {
  run dcr $fixtures_dir/hooks.invalid.php
  [ "$status" -eq 1 ]
}

@test "Multiple invalid review fails" {
  run dcr $fixtures_dir/hooks.invalid.php $fixtures_dir/hooks.invalid2.php
  [ "$status" -eq 1 ]
}

@test "Multiple valid and invalid review fails" {
  run dcr $fixtures_dir/hooks.valid.php $fixtures_dir/hooks.invalid.php
  [ "$status" -eq 1 ]
}

@test "Dir with valid items pass" {
  run dcr $fixtures_dir/valid
  [ "$status" -eq 0 ]
}

@test "Dir with invalid items fail" {
  run dcr $fixtures_dir/invalid
  [ "$status" -eq 1 ]
}

@test "Dir with valid and invalid items fail" {
  run dcr $fixtures_dir
  [ "$status" -eq 1 ]
}
