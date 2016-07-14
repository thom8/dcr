#
# @file
# BATS test helpers.
#

# Guard against bats executing this twice
if [ -z "$TEST_PATH_INITIALIZED" ]; then
  export TEST_PATH_INITIALIZED=true

  # Rewrite environment PATH to make commands isolated.
  PATH=/usr/bin:/usr/local/bin:/bin:/usr/sbin:/sbin
  # Add BATS test directory to the PATH.
  PATH="$(dirname $BATS_TEST_DIRNAME):$PATH"
fi

#
# Output custom string.
#
flunk() {
  { if [ "$#" -eq 0 ]; then cat -
    else echo "$@"
    fi
  } | sed "s:${BATS_TEST_DIRNAME}:TEST_DIR:g" >&2
  return 1
}

#
# Success assertion.
#
assert_success() {
  if [ "$status" -ne 0 ]; then
    flunk "command failed with exit status $status"
  elif [ "$#" -gt 0 ]; then
    assert_output "$1"
  fi
}

#
# Failure assertion.
#
assert_failure() {
  if [ "$status" -eq 0 ]; then
    flunk "expected failed exit status"
  elif [ "$#" -gt 0 ]; then
    assert_output "$1"
  fi
}

#
# Equality assertion.
#
assert_equal() {
  if [ "$1" != "$2" ]; then
    { echo "expected: $1"
      echo "actual:   $2"
    } | flunk
  fi
}

#
# Contains assertion.
#
assert_contains() {
  if [[ "$2" =~ "$1" ]] ; then
    return 0
  else
    { echo "string:   $2"
      echo "contains: $1"
    } | flunk
  fi
}


#
# Assertion output.
#
assert_output() {
  local expected
  if [ $# -eq 0 ]; then expected="$(cat -)"
  else expected="$1"
  fi
  assert_equal "$expected" "$output"
}

#
# Helper to check whether binary file exists.
#
binary_found() {
	if [[ "$1" == "" ]]; then
		return 1;
	fi

	local bpath=`which $1`

	if [[ "$bpath" != "" ]] && [ -f `which $1` ]; then
		return 0
	else
		echo "\"$1\" executable not found."
		return 1
	fi
}

#
# Return random string.
#
random_string() {
  local ret
  ret=$(hexdump -n 16 -v -e '/1 "%02X"' /dev/urandom)
  echo $ret
}

#
# Project root path.
#
function project_root() {
  echo $(pwd)
}
