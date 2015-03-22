#!/usr/bin/env bats

@test "dcr command available in current session" {
    command -v ./dcr
}
