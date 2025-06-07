VENDOR_AUTOLOAD := vendor/autoload.php

help:  ## Print the help documentation
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

$(VENDOR_AUTOLOAD):
	composer install --prefer-dist --no-progress

.PHONY: composer
composer: $(VENDOR_AUTOLOAD) ## Runs composer install

.PHONY: lint
lint: composer ## PHP Lint
	vendor/squizlabs/php_codesniffer/bin/phpcs

.PHONY: fmt
fmt: composer ## PHP Format
	vendor/squizlabs/php_codesniffer/bin/phpcbf

.PHONY: test
test: composer ## PHP Unit Tests
	vendor/bin/phpunit tests

.PHONY: docs
docs:  ## Generate Docs
	docker run --rm -v $(shell pwd):/data phpdoc/phpdoc:3 --ignore=vendor/ run -d src/ -t docs/

.PHONY: docs-serve
docs-serve: docs ## Serve documentation using PHP's built-in server
	php -S localhost:8001 -t docs
