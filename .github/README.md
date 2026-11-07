# GitHub configuration

## GitHub Actions — disabled

This project intentionally **does not use GitHub Actions**.

- Do **not** add workflows under `.github/workflows/`.
- Do **not** enable Actions, Dependabot Actions, or reusable workflow calls for this repository.
- Local development uses Composer, Artisan, Pest, and npm only.

If Actions were ever enabled on a remote host, keep them turned off in the repository settings (**Settings → Actions → General → Disable actions**).
