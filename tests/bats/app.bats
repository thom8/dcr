#!/usr/bin/env bats
#
# App tests for DCR.
#

load test_helper

setup() {
  source ~/.profile
}

@test "Assert custom vendor standards are picked up" {
  run dcr -i
  assert_equal "The installed coding standards are MySource, PEAR, PHPCS, PSR1, PSR2, Squiz, Zend, Drupal and DrupalPractice" "$output"
}
