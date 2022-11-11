# Development for the custom ecms_distribution

Use feature branches for each issue that you work on. When you are ready
to test your changes on the site factory environment, merge your feature
branch to the `staging` branch of this repository. The staging branch
should be reset to the main `branch` ideally after each sprint.

## Testing ecms_profile and ecms_pattern_lab changes

If you need to test a change that impacts both this repo one of the
other repos, you can do so by requiring their respective feature
branches, or their staging branches. Be sure the commits are pushed
to Github and then be sure to update the composer lock file in this
repo to reference the latest commit for the other project(s) you are
testing.

```shell
lando composer update rhodeislandecms/ecms_profile state-of-rhode-island-ecms/ecms_patternlab
```
