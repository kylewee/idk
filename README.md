# idk

This repository is configured for automatic deployment to Netlify using GitHub Actions.

## Required GitHub Repository Secrets

To enable Netlify deployment, you need to add the following secrets to your GitHub repository:

### NETLIFY_AUTH_TOKEN
1. Go to [Netlify User Settings > Applications](https://app.netlify.com/user/applications)
2. Click "New access token"
3. Give it a descriptive name (e.g., "GitHub Actions Deployment")
4. Copy the generated token
5. Add it as a repository secret named `NETLIFY_AUTH_TOKEN`

### NETLIFY_SITE_ID
1. Go to your Netlify site dashboard
2. Navigate to Site settings > General > Site details
3. Copy the "Site ID" value
4. Add it as a repository secret named `NETLIFY_SITE_ID`

## How to Add GitHub Repository Secrets

1. Go to your GitHub repository
2. Click on "Settings" tab
3. In the left sidebar, click "Secrets and variables" → "Actions"
4. Click "New repository secret"
5. Add the secret name and value
6. Click "Add secret"

## Deployment

The site will automatically deploy to Netlify when:
- Code is pushed to the `main` branch
- A pull request is opened against the `main` branch

The deployment workflow is defined in `.github/workflows/deploy.yml` and uses the configuration in `netlify.toml`.