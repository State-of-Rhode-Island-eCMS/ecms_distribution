# Deploying a New Site on Acquia Cloud Site Factory
The following documentation outlines the steps needed when creating
a new site within Acquia Cloud Site Factory (ACSF).

## Add the site via the ACSF dashboard
Using the ACSF dashboard, create the new site within the
"Public Sites" group. The site name will typically match
the site subdomain, e.g. "oha" for "oha.ri.gov".

## Create Users
The new site will include an admin account for the user who created it.
To login for the first time, use the /user/password reset form.
Once you have logged in, add other `Drupal Admin` users that will need
to access the site.

## Request SSO updates
For non-admin users, you must contact the RI DOIT to establish the new
Active Directory groups and domains, as outlined in the distribution
documentation.

## Let's Encrypt - Temporary SSL Setup
Create a temporary certificate for the new production domain using
Let's Encrypt. The [Acquia Let's Encrypt] documentation outlines
this process, but here are specific steps to take that avoid the
need to deploy a temporary branch. This approach utilizes the Redirect
module, which is installed by default on all sites.
 1. [Install certbot] on your local machine
 2. Run the command `sudo certbot --manual certonly`
 3. Follow the prompts, entering the new domain in question
 4. When prompted with the verification requests and contents, login
    to the new site and create a media item with a simple TXT file,
    uploading a file with the contents of the verification data.
 5. Then, create a new redirect at /admin/config/search/redirect/add
    that redirects the verification request to the uploaded file.
    Use a "303 See Other" status code.
 6. Once these are in place, return to the certbot process and continue.
 4. You should see a successful message like so on completion:
```shell
IMPORTANT NOTES:
Congratulations! Your certificate and chain have been saved at:
   /etc/letsencrypt/live/governor.riecms.acsitefactory.com/fullchain.pem
```
 5. To access the certificate on your local machine, run the command (with {DOMAIN} replaced):
   ```shell
   sudo vi /etc/letsencrypt/live/{DOMAIN}/fullchain.pem
   sudo vi /etc/letsencrypt/live/{DOMAIN}/privkey.pem
   ```

 6. Install the new certificate using the Acquia Cloud Dashboard SSL Interface
 7. Follow Acquia's documentation to [activate the new certificate]
 8. Delete the media item and redirect from Drupal

[Acquia Let's Encrypt]: https://support.acquia.com/hc/en-us/articles/360009491013-Using-Let-s-Encrypt-SSL-on-Acquia-Cloud
[Install certbot]: https://certbot.eff.org/lets-encrypt/osx-other
[activate the new certificate]: https://docs.acquia.com/cloud-platform/manage/ssl/cert/#installing-an-ssl-certificate-not-based-on-an-acquia-generated-csr
