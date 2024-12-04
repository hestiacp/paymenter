# Hestia Server module for Paymenter

Opensource module for HestiaCP download via https://github.com/hestiacp/paymenter or via https://market.paymenter.org

# Support

Via https://github.com/hestiacp/paymenter or https://forum.hestiacp.com

# Setup

Create a file in:
/usr/local/hestia/data/api/ named paymenter

With contents:

```
ROLE='admin'
COMMANDS='v-add-user,v-delete-user,v-suspend-user,v-unsuspend-user,v-change-user-shell,v-list-user,v-list-users,v-make-tmp-file,v-add-domain,v-change-user-package,v-make-tmp-file,v-change-user-password,v-list-user-packages'
```

Login with your main Hestia account created during setup: (It is currently by default admin)

Go to Edit user -> Access Key -> Add Access key  and select the paymenter profile

Enter data into settings when you enable this plugin

Then Enable in server settings the API access and add your server IP to white list

# Known Issues

## No email will be send to the client 
Hestia currently doesn't have support for sending email to client with new account via api 
Haven't checked Paymenter if the allow it if we so it needs to be implemented

## Delete "User" / Suspend User will restart Nginx and if paymenter is hosted on the same server connection will get dropped

Don't run it on the same server is the easist fixed for now
