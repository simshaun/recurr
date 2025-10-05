#!/bin/bash

# Check for flags
xdebug_enabled=false
xdebug_coverage=false
xdebug_server_name=""
rerun_failed=false
args=()

for arg in "$@"; do
    if [[ "$arg" == --xdebug=* ]]; then
        xdebug_enabled=true
        xdebug_server_name="${arg#--xdebug=}"
    elif [[ "$arg" == "--xdebug" ]]; then
        xdebug_enabled=true
    elif [[ "$arg" == "--xdebug-coverage" ]]; then
        xdebug_enabled=true
        xdebug_coverage=true
    elif [[ "$arg" == "--rerun" ]] || [[ "$arg" == "--retry" ]]; then
        rerun_failed=true
    else
        args+=("$arg")
    fi
done

if [ "$xdebug_enabled" = true ]; then
    if [ "$xdebug_coverage" = true ]; then
      export XDEBUG_MODE="coverage"
    else
      export XDEBUG_MODE="debug"
    fi
    export XDEBUG_SESSION="1"
    if [ -n "$xdebug_server_name" ]; then
        export PHP_IDE_CONFIG="serverName=$xdebug_server_name"
    fi
    # This file was replicated from the PowerShell test.ps1 file.
    # However, we assume this file will be ran within a Docker shell,
    # so the line below isn't needed.
    #export XDEBUG_CONFIG="client_host=127.0.0.1 client_port=9005"
else
    unset XDEBUG_MODE
    unset XDEBUG_SESSION
    unset XDEBUG_CONFIG
fi

# Function to extract failing test names from TeamCity output
extract_failing_tests() {
    if [ ! -f ".phpunit.result.teamcity.txt" ]; then
        echo "Error: .phpunit.result.teamcity.txt not found. Run tests first to generate the TeamCity report."
        exit 1
    fi

    failing_tests=$(python3 -c "
import re
with open('.phpunit.result.teamcity.txt') as f:
    lines = f.readlines()
for i, line in enumerate(lines):
    if 'testFailed' in line and i > 0 and 'testStarted' in lines[i-1]:
        match = re.search(r'locationHint=.*?::\\\\([^:]+::[^\']+)', lines[i-1])
        if match:
            print(match.group(1))
      ")

    if [ -z "$failing_tests" ]; then
        echo "No failing tests found in .phpunit.result.teamcity.txt"
        exit 0
    fi

    echo "Rerunning failing tests:"
    echo "$failing_tests"
    return 0
}

# Build phpunit command with coverage if needed
coverage_args=""
if [ "$xdebug_coverage" = true ]; then
    coverage_args="--coverage-xml .phpunit-coverage"
fi

# Run tests
if [ "$rerun_failed" = true ]; then
    extract_failing_tests

    # Build pipe-separated filter pattern with escaped backslashes
    filter_pattern=$(printf '%s\n' $failing_tests | \
        sed 's/\\/\\\\/g' | \
        paste -sd '|')

    if [ -n "$filter_pattern" ]; then
        ./backend/vendor/bin/phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverage_args --filter "$filter_pattern" backend/src
    fi
elif [ ${#args[@]} -eq 0 ]; then
    ./backend/vendor/bin/phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverage_args backend/src
else
    filter="${args[0]}"
    ./backend/vendor/bin/phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverage_args --filter "$filter" backend/src
fi
