# Parse command line arguments
$xdebugEnabled = $false
$xdebugCoverage = $false
$xdebugServerName = ""
$rerunFailed = $false
$testArgs = @()

foreach ($arg in $args) {
    if ($arg -like "--xdebug=*") {
        $xdebugEnabled = $true
        $xdebugServerName = $arg.Substring(9)
    } elseif ($arg -eq "--xdebug") {
        $xdebugEnabled = $true
    } elseif ($arg -eq "--xdebug-coverage") {
        $xdebugEnabled = $true
        $xdebugCoverage = $true
    } elseif ($arg -eq "--rerun" -or $arg -eq "--retry") {
        $rerunFailed = $true
    } else {
        $testArgs += $arg
    }
}

# Set or clear Xdebug environment variables
if ($xdebugEnabled) {
    if ($xdebugCoverage) {
        $env:XDEBUG_MODE = "coverage"
    } else {
        $env:XDEBUG_MODE = "debug"
    }
    $env:XDEBUG_SESSION = "1"
    if ($xdebugServerName) {
        $env:PHP_IDE_CONFIG = "serverName=$xdebugServerName"
    }
    $env:XDEBUG_CONFIG = "client_host=127.0.0.1 client_port=9005"
} else {
    Remove-Item Env:XDEBUG_MODE -ErrorAction SilentlyContinue
    Remove-Item Env:XDEBUG_SESSION -ErrorAction SilentlyContinue
    Remove-Item Env:XDEBUG_CONFIG -ErrorAction SilentlyContinue
    Remove-Item Env:PHP_IDE_CONFIG -ErrorAction SilentlyContinue
}

# Function to extract failing test names from TeamCity output
function Extract-FailingTests {
    if (-not (Test-Path ".phpunit.result.teamcity.txt")) {
        Write-Host "Error: .phpunit.result.teamcity.txt not found. Run tests first to generate the TeamCity report."
        exit 1
    }

    # Find testFailed lines with context
    $failedLines = Select-String -Path ".phpunit.result.teamcity.txt" -Pattern "testFailed" -Context 1,0
    $failingTests = @()

    foreach ($match in $failedLines) {
        $prevLine = $match.Context.PreContext[0]
        if ($prevLine -match "locationHint='php_qn:.*?::(.+?)'") {
            $testName = $matches[1] -replace '^\\', ''
            $failingTests += $testName
        }
    }

    $failingTests = $failingTests | Sort-Object -Unique

    if (-not $failingTests) {
        Write-Host "No failing tests found in .phpunit.result.teamcity.txt"
        exit 0
    }

    # Escape backslashes for PHPUnit regex filter
    $filterPattern = ($failingTests | ForEach-Object { $_ -replace '\\', '\\' }) -join '|'

    Write-Host "Rerunning failing tests:"
    $failingTests | ForEach-Object { Write-Host $_ }

    return $filterPattern
}

# Build phpunit command with coverage if needed
$coverageArgs = ""
if ($xdebugCoverage) {
    $coverageArgs = "--coverage-xml .phpunit-coverage"
}

# Run tests
if ($rerunFailed) {
    $filterPattern = Extract-FailingTests
    if ($filterPattern) {
        .\vendor\bin\phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverageArgs --filter "'`"$filterPattern`"'"
    }
} elseif (-not $testArgs) {
    .\vendor\bin\phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverageArgs
} else {
    $filter = $testArgs[0]
    .\vendor\bin\phpunit --stop-on-failure -c phpunit.xml.dist --display-warnings $coverageArgs --filter $filter
}
