#!/usr/bin/env bash
# Releases a new plugin version: stamps the version from the argument into the
# plugin header, commits, tags, resets the header back to "develop" and pushes
# branch + tag in one go. The tag push triggers the release job in ci.yml.
set -euo pipefail

VERSION="${1:?Usage: bin/release.sh <version>  (e.g. 0.1.7)}"

# Plain semver without v prefix, matching the tag patterns in ci.yml.
[[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+(-[0-9A-Za-z.]+)?$ ]] \
  || { echo "Not a plain semver version (no v prefix): $VERSION" >&2; exit 1; }

cd "$(dirname "$0")/.."

[[ -z "$(git status --porcelain)" ]] \
  || { echo "Working tree is not clean" >&2; exit 1; }
[[ "$(git branch --show-current)" == "master" ]] \
  || { echo "Not on master" >&2; exit 1; }
git fetch origin
[[ "$(git rev-parse HEAD)" == "$(git rev-parse origin/master)" ]] \
  || { echo "master is not in sync with origin/master" >&2; exit 1; }
! git rev-parse -q --verify "refs/tags/$VERSION" >/dev/null \
  || { echo "Tag $VERSION already exists" >&2; exit 1; }

sed -i "s/^ \* Version:.*/ * Version:      $VERSION/" object-storage.php
git commit -am "Release $VERSION"
git tag "$VERSION"

sed -i "s/^ \* Version:.*/ * Version:      develop/" object-storage.php
git commit -am "Back to develop"

git push origin master "$VERSION"

echo "Released $VERSION - CI will build the zip and create the GitHub release."
