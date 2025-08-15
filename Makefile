.PHONY: all
all: setup check test

.PHONY: setup
setup:
	@composer install

.PHONY: check
check: psalm bom

.PHONY: bom
bom:
	@rm -f build/reports/bom.json
	@mkdir -p build/reports
	composer CycloneDX:make-sbom --output-format=JSON --output-file=build/reports/bom.json --no-interaction

.PHONY: test
test:
	@./vendor/bin/phpunit --coverage-clover=reports/clover.xml

.PHONY: psalm
psalm:
	@./vendor/bin/psalm

.PHONY: psalm.ignoreBaseline
psalm.ignoreBaseline:
	@./vendor/bin/psalm --ignore-baseline

.PHONY: psalm.updateBaseline
psalm.updateBaseline:
	@./vendor/bin/psalm --no-diff --no-cache --update-baseline
