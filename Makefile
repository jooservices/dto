.PHONY: build install hooks-install shell validate lint test test-hooks test-coverage ci audit bench \
	scan scan-secrets scan-dependencies scan-sast scan-config scan-workflows scan-image sonar sonar-up sonar-stop preflight

DOCKER_COMPOSE ?= tools/ci/docker-compose
PHP = tools/run-php
TOOLS = $(DOCKER_COMPOSE) --profile tools run --rm --no-deps

build:
	$(DOCKER_COMPOSE) --profile tools build php sonar-scanner

install: build
	$(PHP) composer install --no-interaction --prefer-dist

hooks-install:
	tools/install-git-hooks

shell:
	tools/run-php bash

validate:
	$(PHP) composer validate --strict

lint:
	$(PHP) composer lint

test:
	$(PHP) composer test

test-hooks:
	tools/test-install-git-hooks

test-coverage:
	$(PHP) composer test:coverage

audit:
	$(PHP) composer audit

bench:
	$(PHP) composer bench

ci:
	$(PHP) composer ci

scan-secrets:
	tools/run-gitleaks staged
	tools/run-gitleaks history

scan-dependencies:
	$(TOOLS) osv-scanner scan source --recursive /workspace/project

scan-sast:
	tools/run-semgrep

scan-config: test-hooks
	$(TOOLS) shellcheck -x \
		/workspace/project/tools/ci/docker-compose \
		/workspace/project/tools/install-git-hooks \
		/workspace/project/tools/php \
		/workspace/project/tools/php-cs-fixer \
		/workspace/project/tools/phpcbf \
		/workspace/project/tools/phpcs \
		/workspace/project/tools/phpdoc \
		/workspace/project/tools/phpmd \
		/workspace/project/tools/phpstan \
		/workspace/project/tools/pint \
		/workspace/project/tools/run-gitleaks \
		/workspace/project/tools/run-php \
		/workspace/project/tools/hooks/commit-msg \
		/workspace/project/tools/hooks/pre-commit \
		/workspace/project/tools/hooks/pre-push \
		/workspace/project/tools/test-install-git-hooks \
		/workspace/project/tools/run-semgrep \
		/workspace/project/tools/run-sonar \
		/workspace/project/tools/run-sonar-server \
		/workspace/project/tools/run-sonar-cloud \
		/workspace/project/tools/ci/run-sonar-scanner \
		/workspace/project/tools/run-zizmor \
		/workspace/project/tools/scan-image \
		/workspace/project/docker/sonar-scanner/entrypoint.sh
	$(TOOLS) hadolint --failure-threshold error \
		/workspace/project/Dockerfile \
		/workspace/project/docker/sonar-scanner/Dockerfile
	$(TOOLS) trivy fs \
		--scanners vuln,misconfig,secret \
		--severity CRITICAL,HIGH \
		--exit-code 1 \
		/workspace/project

scan-workflows:
	$(TOOLS) actionlint -color
	tools/run-zizmor

scan-image: build
	tools/scan-image

scan: scan-secrets scan-dependencies scan-sast scan-config scan-workflows scan-image

sonar-up:
	tools/run-sonar-server up

sonar-stop:
	tools/run-sonar-server stop

sonar: ci
	tools/run-sonar

preflight: build validate ci audit scan sonar
