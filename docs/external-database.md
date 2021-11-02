## External Database

One external database connection is available for each site factory environment. The connection settings are
included in the /factory-hooks/post-settings-php/z_database_settings.php file. Three environment variables
are required to establish a connection to an Acquia provided database. Localhost is assumed as the host.

### Creating a new database

A new site factory site instance must be created manually using the Site Factory dashboard. This will perform
the complete Drupal 9 site install. Once that is complete, the database name and credentials will be available
in the Acquia Cloud dashboard. Those values should be used to populate the 3 environment variables defined
in the secrets.settings.php file for the given environment.

```php
  putenv("EXTERNAL_DATABASE_NAME=possum");
  putenv("EXTERNAL_DATABASE_USER=llama");
  putenv("EXTERNAL_DATABASE_PASSWORD=sloth");
```

### Populating the new database with your custom data

Make sure you have SSH access to the destination environment. This will require setup from a Site Factory admin.
Create a MySQL connection to the new database using the SQL utility of your choice. The MySQL Host information
can be found in the Acquia Cloud dashboard under Databases > {DATABASE_NAME} > Details, e.g. `staging-4878`

Once you have a connection established, you can execute SQL queries as needed to populate the database.

### Usage

The Views Database Connector module is part of the codebase and available for installation on any site.
A user with module administrator permissions must manually enable the module. Once installed, follow these steps
to create a new Drupal view using one of the database tables you created above.

1. Add a new View: /admin/structure/views/add
2. Under the "Show" dropdown, you should see options for all of your tables, prefaced with "[VDC]"
3. Add filters and fields as normal
4. Keep in mind that if you add a new table, the module will need to be reinstalled in order to see the new data
5. Adding or deleting rows to an existing table will be reflected in existing views
   1. This is important for any longer term/production level solution
