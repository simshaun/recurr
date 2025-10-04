# If no arguments, run all tests
if (-not $args)
{
    .\vendor\bin\phpstan analyse --no-ansi --no-progress --error-format=table
}
else
{
    $path = $args[0]
    .\vendor\bin\phpstan analyse --no-ansi --no-progress --error-format=table $path
}
