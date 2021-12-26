# Temporal PHP SDK

[![CI Status](https://github.com/temporalio/php-sdk/workflows/Unit/badge.svg)](https://github.com/temporalio/php-sdk/actions)
[![Stable Release](https://poser.pugx.org/temporal/sdk/version)](https://packagist.org/packages/temporal/sdk)
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Ftemporalio%2Fsdk-php.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Ftemporalio%2Fsdk-php?ref=badge_shield)

## Introduction

Temporal is a distributed, scalable, durable, and highly available orchestration
engine used to execute asynchronous long-running business logic in a scalable
and resilient way.

"Temporal PHP SDK" is the framework for authoring Workflows and Activities using
PHP language.

## Installation

SDK is available as composer package and can be installed using the
following command in a root of your project:

```bash
$ composer require temporal/sdk
```

Make sure to install [RoadRunner](https://github.com/spiral/roadrunner) to enable Workflow and Activity consumption in your PHP Workers.

## Usage

See [examples](https://github.com/temporalio/samples-php) to get started.

## Documentation

The documentation on how to use the Temporal PHP SDK and client is [here](https://docs.temporal.io/docs/php/introduction).

## Contributing

Install dependencies:

```sh
brew install php
brew install composer
git clone https://github.com/temporalio/sdk-php.git
cd sdk-php
composer install
```

Lint and test:

```sh
composer run-script check
composer run-script phpcs
composer run-script tests
```

Unit and functional tests can also be run separately:

```sh
vendor/bin/phpunit --testsuite=Unit --testdox --verbose
vendor/bin/phpunit --testsuite=Functional --testdox --verbose
```

## License

MIT License, please see [LICENSE](LICENSE.md) for details.

[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Ftemporalio%2Fsdk-php.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Ftemporalio%2Fsdk-php?ref=badge_large)