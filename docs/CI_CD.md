# CI/CD (GitHub Actions) Setup

This project includes a sample GitHub Actions workflow at `.github/workflows/ci.yml` that:

- Checks out the code
- Sets up PHP 8.1
- Installs Composer dependencies
- Runs PHP linting and PHPUnit tests

To enable:
1. Push this repository to GitHub.
2. Enable Actions for the repo.
3. Ensure `composer install` works on CI (add `composer.lock` if desired).

Customizations:
- Add deployment steps (SCP, rsync, FTP, or use deployment actions) to deploy to your server.
- Add environment secrets (`DB_HOST`, `DB_USER`, `DB_PASS`) in GitHub repo settings and reference them in workflow.
