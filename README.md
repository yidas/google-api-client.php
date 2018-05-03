Google API Client PHP Sample
============================

Google API Client Libraries with PHP sample code




---

Installation
------------

1. **Deploy code to PHP Web server**  
  The Document Root could be `{documentRoot}/{thisPackage}/www`.

2. **Setting Google API Console**  
  In [Google API Console](https://console.developers.google.com), you need to set a credential including pointing web root URL to **Authorized redirect URIs** likes `http://{host}/{thisPackage}/www`, and then enable APIs such as Google+ API, Calendar API and Drive API in Library.

3. **Download and place Credential JSON**  
  Download the credential JSON file then rename and place it to `{documentRoot}/{thisPackage}/files/client_secret.json`.

---

Libraries / Components inclusion
--------------------------------

- **Google API Client Provider** (Client Handler)  
  Provide interface and the cache of Client that help to develop Google API OAuth with getting services.
  
- **Google API Client Model**  
  Handling Google API Access Token for save, get and delete with extensible Data Store Carrier.
  
- **Google Calendar API Component**  

---

Google Services demonstration
-----------------------------

- **Google Plus**  
  For getting Google User information.
    
- **Calendar**

- **Drive**

---

ADDITIONS
---------


### Guzzle SSL Verify Problem

If you are using Windows as service server, you may deal with [SSL certificate problem](https://github.com/guzzle/guzzle/issues/394).

This problem could be solved by modifing `/google-api/vendor/google/apiclient/src/Google/Client.php`:

```php
// For windows PHP cURL
>$options['verify'] = false;
```
