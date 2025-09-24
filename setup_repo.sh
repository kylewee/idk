#!/usr/bin/env bash
set -e

REPO="kylewee/wtf"
HTTPS_REMOTE="https://github.com/${REPO}.git"
SSH_REMOTE="git@github.com:${REPO}.git"

echo "1/6 create .gitignore"
cat > .gitignore <<'EOF'
node_modules/
dist/
.build/
.env
.vscode/
EOF

echo "2/6 remove stray submodule metadata (quote-app)"
rm -rf quote-app/.git 2>/dev/null || true
git rm --cached -r quote-app 2>/dev/null || true
rm -f .gitmodules 2>/dev/null || true

if [ ! -d .git ]; then
  echo "3/6 init git"
  git init
fi

echo "4/6 add and commit"
git add .
# only commit if there is something to commit
if git diff --cached --quiet; then
  echo "nothing staged to commit"
else
  git commit -m "Initial import"
fi

echo "5/6 set main branch"
git branch -M main || git branch -m main || true

# try GitHub CLI first
if command -v gh >/dev/null 2>&1; then
  echo "6/6 attempting to create repo with gh..."
  if gh repo create "$REPO" --public --source=. --remote=origin --push 2>/dev/null; then
    echo "Repo created and pushed with gh."
    exit 0
  else
    echo "gh present but create failed or repo exists. Trying to set remote and push."
  fi
fi

# fallback: set HTTPS remote and push (will prompt for credentials)
if git remote | grep -q origin; then
  echo "origin exists. Showing remotes:"
  git remote -v
else
  echo "setting HTTPS remote -> $HTTPS_REMOTE"
  git remote add origin "$HTTPS_REMOTE"
fi

echo "trying push to origin main (HTTPS). This will prompt for username and token/password if needed."
if git push -u origin main; then
  echo "pushed to origin via HTTPS."
  exit 0
fi

echo "push failed. Next steps:"
echo " - If you want SSH, run:"
echo "   ssh-keygen -t ed25519 -C 'your_email@example.com' -f ~/.ssh/id_ed25519 -N ''"
echo "   eval \"\$(ssh-agent -s)\" && ssh-add ~/.ssh/id_ed25519"
echo "   cat ~/.ssh/id_ed25519.pub  # paste into GitHub > Settings > SSH keys"
echo "   git remote set-url origin $SSH_REMOTE"
echo "   git push -u origin main"
echo ""
echo " - Or create the repo on GitHub web (https://github.com/new) then run:"
echo "   git remote set-url origin $HTTPS_REMOTE"
echo "   git push -u origin main"

exit 1
