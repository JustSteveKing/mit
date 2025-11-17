# Contributing

Thank you for considering contributing to Mit — your help improves the project for everyone.

How to contribute

1. Fork the repository and create a branch for your change.
2. Make your changes in a feature branch with a clear name (e.g., `fix/clear-listeners` or `feat/priority-sorting`).
3. Ensure tests are added for new behavior and existing tests pass.
4. Follow PSR-12 coding style. Use `./vendor/bin/pint` to format locally.
5. Create a Pull Request with a clear description of the change, motivation, and a minimal reproduction if applicable.

Testing locally

```bash
composer install --no-interaction --prefer-dist
vendor/bin/pint --test
vendor/bin/phpstan analyse
vendor/bin/psalm
vendor/bin/phpunit
```

Code of conduct

Please follow the project's Code of Conduct in `CODE_OF_CONDUCT.md`.

License and CLA

By contributing you agree that your contributions will be licensed under the project's MIT license. No separate CLA is required.
