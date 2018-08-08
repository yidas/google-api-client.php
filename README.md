Google API Client PHP Sample
============================

Google API Client Libraries with Google service samples for PHP

[![Latest Stable Version](https://poser.pugx.org/yidas/google-api-sample/v/stable?format=flat-square)](https://packagist.org/packages/yidas/google-api-sample)
[![Latest Unstable Version](https://poser.pugx.org/yidas/google-api-sample/v/unstable?format=flat-square)](https://packagist.org/packages/yidas/google-api-sample)
[![License](https://poser.pugx.org/yidas/google-api-sample/license?format=flat-square)](https://packagist.org/packages/yidas/google-api-sample)

---

INSTALLATION
------------

### 1. Download the Project 

Composer download:

```bash
composer create-project --prefer-dist yidas/google-api-sample
```

> You could download by git clone or by zip file alternatively.


### 2. Google API Credential

In [Google API Console](https://console.developers.google.com), you need to set a credential including pointing web root URL to **Authorized redirect URIs** likes `http://{thisPackage}/www/callback.php`, and then enable APIs such as Google+ API, Calendar API and Drive API in Library.

Then download the credential JSON file then rename and place it to `{thisPackage}/files/client_secret.json`.

---

LIBRARIES INCLUSION
-------------------

- **User Component**

- **Google Calendar API Component**  

---

GOOGLE SERVICES DEMONSTRATION
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

This problem could be solved by modifing `vendor/google/apiclient/src/Google/Client.php`:

```php
// For windows PHP cURL
$options['verify'] = false;
```

### This app isn’t verified Problem

[Google – OAuth Client Verification](https://developers.google.com/apps-script/guides/client-verification)
