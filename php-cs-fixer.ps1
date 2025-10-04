# If no arguments, run all tests
if (-not $args)
{
    .\vendor\bin\php-cs-fixer fix
}
else
{
    $path = $args[0]
    .\vendor\bin\php-cs-fixer fix $path
}
