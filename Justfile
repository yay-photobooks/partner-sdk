set dotenv-load := false
set positional-arguments

export COLUMNS := '550'

default:
  @just --list

php := "php"
composer := "composer"

# Install dependencies
install:
    {{ composer }} install

# Run PHPStan static analysis
phpstan *args='':
    {{ php }} vendor/bin/phpstan "${@}"

# Watch files and run PHPStan on changes
watch-phpstan *args='':
    find src/ tests/ -name '*.php' | entr {{ php }} vendor/bin/phpstan "${@}"

# Run PHPUnit tests
phpunit *args='':
    {{ php }} vendor/bin/phpunit "${@}"

# Fix code style with PHP-CS-Fixer
fix *args='-v':
    {{ php }} vendor/bin/php-cs-fixer fix "${@}"

# Run composer commands
composer *args='':
    {{ composer }} "${@}"

# Generate PHPStan baseline
phpstan-baseline:
    just phpstan analyse --generate-baseline=phpstan-baseline.neon

# Run all quality checks
check:
    just phpstan
    just phpunit

# Prepare for commit (fix style, run checks)
prep:
    just fix
    just check

# Run the example
example:
    {{ php }} example.php

# Show current configuration
config:
    @echo "Environment: ${YAY_PARTNER_ENVIRONMENT:-not set}"
    @echo "Username: ${YAY_PARTNER_USERNAME:-not set}"
    @echo "User Agent: ${YAY_PARTNER_USER_AGENT:-not set}"
