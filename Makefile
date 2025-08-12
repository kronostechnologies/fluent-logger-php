.PHONY: all
all: setup test

.PHONY: setup
setup:
	@composer install

.PHONY: test
test:
	@./vendor/bin/phpunit
