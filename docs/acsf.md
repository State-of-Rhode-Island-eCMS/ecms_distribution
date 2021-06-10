# Updating the Acquia Cloud Site Factory Module
The following documentation outlines the steps needed when updating
the Acquia Cloud Site Factory (ACSF) contrib module.

## acsf-init and acsf-init-verify drush commands
Per the module readme and Acquia's [Updating ACSF] documentation,
you must execute the acsf-init and acsf-init-verify each time the
module is updated.
Update the module using composer as normal, then execute the
following locally:
```shell
 lando drush --include="/app/docroot/modules/contrib/acsf/acsf_init" acsf-init -y
 lando drush --include="/app/docroot/modules/contrib/acsf/acsf_init" acsf-init-verify
```


[Updating ACSF]: https://docs.acquia.com/site-factory/workflow/deployments/acsf-init/
