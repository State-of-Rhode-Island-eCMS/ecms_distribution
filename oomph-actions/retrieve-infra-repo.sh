#!/usr/bin/env bash

# This script will retrieve the oomphinc/oomph-infrastructure repository.
# To add this to a CI/CD pipeline, copy this script into the project repo
# and add an execution step that runs it. You will need to provide a deploy
# key as an environment variable (INFRA_REPO_DEPLOY_KEY) and a valid git
# branch or tag name as an argument.
#
# An example implementation in GitHub Actions:
#   jobs:
#     example:
#       name: Example
#       runs-on: ubuntu-18.04
#       steps:
#         - name: Retrieve the infrastructure repo
#           env:
#             INFRA_REPO_DEPLOY_KEY: ${{ secrets.INFRA_REPO_DEPLOY_KEY }}
#           run: ./bin/retrieve-infra-repo.sh master

# Ensure the necessary envvar exists
if [[ -z "${INFRA_REPO_DEPLOY_KEY}" ]]; then
  echo "The INFRA_REPO_DEPLOY_KEY does not exist or is empty!" >&2
  exit 1
fi

# Ensure the correct number of arguments have been passed
if [ "$#" -ne 1 ]; then
  echo "No branch or tag name was provided!" >&2
  exit 1
else
  branch="$1"
fi

# Start the SSH agent
eval "$(ssh-agent -s)"

# Add the deploy key to the SSH agent
ssh-add - <<< "${INFRA_REPO_DEPLOY_KEY}"

# Ensure host key checking succeeds
mkdir -p ${HOME}/.ssh
ssh-keyscan github.com >> ${HOME}/.ssh/known_hosts

# Clone the infrastructure repo using the target ref
git clone -b ${branch} git@github.com:oomphinc/oomph-infrastructure.git
