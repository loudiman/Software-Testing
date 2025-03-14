# GitHub Testing Framework

A comprehensive testing suite for GitHub's login functionality and homepage performance using PHPUnit and PHP WebDriver.

## Overview

This project provides automated tests for GitHub's web interface, focusing on:

- Login functionality validation
- Homepage performance metrics
- Reliability testing
- Stress testing

## Prerequisites

- PHP 7.4 or higher
- Composer
- Chrome/Firefox WebDriver

## Installation

1. Clone this repository
2. Install dependencies:

```bash
composer install
```

3. Configure WebDriver (ensure browser driver is in PATH)

## Test Suite Description

### Login Tests

- **[GitHubLoginSmokeTest.php](tests/GitHubLoginSmokeTest.php)**: Basic login functionality verification
- **[GitHubLoginValidationTest.php](tests/GitHubLoginValidationTest.php)**: Input validation and error handling tests

### Performance Tests

- **[GitHubHomePagePerformanceTest.php](tests/GitHubHomePagePerformanceTest.php)**: Measures loading time and resource usage of GitHub homepage
- **[GitHubStressTest.php](tests/GitHubStressTest.php)**: Evaluates system behavior under high load

### Reliability Tests

- **[GitHubReliabilityTest.php](tests/GitHubReliabilityTest.php)**: Long-running tests to detect intermittent issues

## Running Tests

Run all tests:

```bash
./vendor/bin/phpunit
```

Run specific test suite:

```bash
./vendor/bin/phpunit --testsuite login
```

Run single test:

```bash
./vendor/bin/phpunit tests/GitHubLoginSmokeTest.php
```

## Test Reports

Test reports are generated in the `output/` directory:
- `homepage_performance_report.json`: Performance metrics for GitHub homepage
- `reliability_test_report.json`: Details on system stability
- `stress_test_metrics.log`: Raw data from stress testing

## Documentation

The repository includes a detailed guide `Software-Testing-PHPUnit.pdf` that covers:

- PHPUnit fundamentals and best practices
- Web testing strategies using PHP WebDriver
- Implementing test suites for web applications
- Analyzing and interpreting test results
- Automated reporting and continuous integration setup

This document serves as both a reference guide and tutorial for extending the testing framework.

## Services

The framework includes a [TransactionService](src/TransactionService.php) for simulating and tracking test transactions.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
