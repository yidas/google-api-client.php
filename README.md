# Google-API-Client.php

Google API Client Libraries PHP Demo code

> Note: The google/apiclient vendor has already modified that turn off the Guzzle Verfify for Windows PHP in `/google-api/vendor/google/apiclient/src/Google/Client.php`
>```
>// For windows PHP cURL
>$options['verify'] = false;
>```

---

## Installation

1. **Deploy code to PHP Web server**  
  The Document Root could be `{documentRoot}/{thisPackage}/www`.

2. **Setting Google API Console**  
  In [Google API Console](https://console.developers.google.com), you need to set a credential including pointing web root URL to **Authorized redirect URIs** likes `http://{host}/{thisPackage}/www`, and then enable APIs such as Google+ API, Calendar API and Drive API in Library.

3. **Download and place Credential JSON**  
  Download the credential JSON file then rename and place it to `{documentRoot}/{thisPackage}/files/client_secret.json`.

---

## Services

- Google Plus  
  For getting Google User information.
    
- Calendar

- Drive
