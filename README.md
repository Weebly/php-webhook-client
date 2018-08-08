# php-webhook-client

This is a simple webhook listener client written in PHP as example server-side code for the Weebly Developer Platform. More information about the platform can be found at https://dev.weebly.com.

The server does two main things:

1. Acts as a server to facilitate an OAuth handshake between Weebly and your app
2. Provides an endpoint for webhook-enabled apps

### Requirements

* Docker installed locally
* Heroku Toolbelt installed locally
* Heroku.com account
* Git installed locally
* Github.com account
* Weebly Free Developer Account

# Usage

* [Heroku One-Button Quickstart](#heroku-one-button-quickstart)
* [Running with Heroku Container Registry](#running-with-heroku-container-registry)
* [Heroku Local and ngrok](#running-locally-with-heroku-local-and-ngrok)

## Heroku One-Button Quickstart

Make sure you have the following data available:

* Weebly App Name
* Weebly App - Client ID
* Weebly App - Secret

> NOTE: You obtain Weebly API Keys for your app from the [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/)

1. Click on the "Deploy to Heroku" button below, and then update the config vars with the respective values.

[![Deploy](https://www.herokucdn.com/deploy/button.svg)](https://heroku.com/deploy)

2. Rename `env.tmpl` file to `.env` and edit the file to add the Weebly API Keys, replace the OAuth Callback URL, and Webhook Callback URL.

In the `.env` file, replace **WEEBLY_CLIENT_ID** and **WEEBLY_APP_SECRET** values respectively (removing the template string tags `{{` `}}` from the values.

For example, if your client id is **123456789**, you would change this line:

`"client_id": "{{YOUR_CLIENT_ID}}",` to look like this: `"client_id": "123456789",`

Save your changes, and close the `.env` file.

3. Update `manifest.json` file.

Use the secured URL you receive for your new Heroku app (once you have finished installing and deploying it), and replace the base URL **callback_url** and **webhook_callback_url** (leave the paths in tact)

For example, if your heroku app URL is: **https://custom-jello-123.herokuapp.com**, you would change this line:

`"callback_url": "{{YOUR_SECURE_APP_ROOT_URL}}/oauth/phase-one",` to look like this: `"callback_url": "https://custom-jello-123.herokuapp.com/oauth/phase-one", 

Once you have updated both the `callback_url` and the `webhook_callback_url` values, save and exit the `manifest.json` file.

4. Create a ZIP archive of the: `manifest.json`, `icon.svg` files.

5. Login to [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/) and upload a new version of your app (selecting the ZIP file you just created)

6. Install the new draft version of your app into your Weebly Developer Test Site, and view the logs in heroku

`heroku logs --tail`

7. You can generate webhook events by logging into and out of your [Weebly Developer Site](https://weebly.com) account.

8. Open your heroku app and if you see "It works!" the app is running.

From here, you would want to update the `app/webhooks-router.js` file with logic to do more than just log the events to the console.

If you use the Quickstart to run this app, the following directions for use with Heroku Local and Heroku Container Registry may not operate as expected.

## Running with Heroku Container Registry

* [Heroku Container Registry docs](https://devcenter.heroku.com/articles/container-registry-and-runtime)

1. Clone the code base to your local machine

`git clone https://github.com/weebly/node-webhook-client`

2. Change your present working directory (PWD) into the newly cloned repository directory

`cd node-webhook-client/`

3. Login to Heroku and Heroku Container, to obtain the URL for your remote Heroku app

`heroku login`

`heroku container:login`

`heroku create` (copy the secured URL beginning with **https://** you will need that to update the `manifest.json` later) 

4. Rename `env.tmpl` file to `.env` and edit the file to add the Weebly API Keys, replace the OAuth Callback URL, and Webhook Callback URL.

You obtain Weebly API Keys for your app from the [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/). In the `.env` file, replace the value for **WEEBLY_CLIENT_ID** with your app's Client ID (making sure to remove template string tags `{{` `}}`` from the values.

For example, if your client id is **123456789**, you would change this line:

`"client_id": "{{YOUR_CLIENT_ID}}",` to look like this: `"client_id": "123456789",`

Update Your Secured App Root URL you obtained from the `heroku create` command and replace the the root URL respectively for: **oauth_callback_url** and **webhook_callback_url** (leave the paths in tact)

5. Set your Weebly API Keys as environment variables on the Heroku app

`heroku config:set WEEBLY_CLIENT_ID=[your_app_client_id]`
`heroku config:set WEEBLY_SECRET_KEY=[your_app_secret_key]`

6. Build the image and push to Container Registry

`heroku container:push web`

7. Release the image to your app

`heroku container: release web`

8. Open the app in your browser

`heroku open`

9. Update `manifest.json` file.

Use the secured URL you receive for your new Heroku app (once you have finished installing and deploying it), and replace the base URL **callback_url** and **webhook_callback_url** (leave the paths in tact)

For example, if your heroku app URL is: **https://custom-jello-123.herokuapp.com**, you would change this line:

`"callback_url": "{{YOUR_SECURE_APP_ROOT_URL}}/oauth/phase-one",` to look like this: `"callback_url": "https://custom-jello-123.herokuapp.com/oauth/phase-one", 

Once you have updated both the `callback_url` and the `webhook_callback_url` values, save and exit the `manifest.json` file.

10. Create a ZIP archive of the: `manifest.json`, `icon.svg` files.

11. Login to [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/) and upload a new version of your app (selecting the ZIP file you just created)

12. Install the new draft version of your app into your Weebly Developer Test Site, and view the logs in your console

13. You can generate webhook events by logging into and out of your [Weebly Developer Site](https://weebly.com) account.

14. Open your heroku app and if you see "It works!" the app is running.

From here, you would want to update the `app/webhooks-router.js` file with logic to do more than just log the events to the console.



## Running locally with Heroku Local and ngrok

Make sure you have an [ngrok](https://ngrok.com) account and that it is installed on your local workstation.

* [Heroku Local docs](https://devcenter.heroku.com/articles/heroku-local)
* [ngrok docs](https://ngrok.com/docs)

1. Clone the code base to your local machine

`git clone https://github.com/weebly/node-webhook-client`

2. Change your present working directory (PWD) into the newly cloned repository directory

`cd node-webhook-client/`

4. Rename `env.tmpl` file to `.env` and edit the file to add the Weebly API Keys, replace the OAuth Callback URL, and Webhook Callback URL.

You obtain Weebly API Keys for your app from the [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/). In the `.env` file, replace the value for **WEEBLY_CLIENT_ID** with your app's Client ID (making sure to remove template string tags `{{` `}}`` from the values.

For example, if your client id is **123456789**, you would change this line:

`"client_id": "{{YOUR_CLIENT_ID}}",` to look like this: `"client_id": "123456789",`

Update Your Secured App Root URL you obtained from **ngrok** and replace the the root URL respectively for: **oauth_callback_url** and **webhook_callback_url** (leave the paths in tact)

5. Update `manifest.json` file.

Use the secured URL you receive for your new Heroku app (once you have finished installing and deploying it), and replace the base URL **callback_url** and **webhook_callback_url** (leave the paths in tact)

For example, if your heroku app URL is: **https://custom-jello-123.herokuapp.com**, you would change this line:

`"callback_url": "{{YOUR_SECURE_APP_ROOT_URL}}/oauth/phase-one",` to look like this: `"callback_url": "https://custom-jello-123.herokuapp.com/oauth/phase-one", 

Once you have updated both the `callback_url` and the `webhook_callback_url` values, save and exit the `manifest.json` file.

6. Create a ZIP archive of the: `manifest.json`, `icon.svg` files.

7. Login to [Weebly Developer Admin Portal](https://www.weebly.com/developer-admin/) and upload a new version of your app (selecting the ZIP file you just created)

8. Start **ngrok** for your app

9. Start your app: `heroku local` (depending upon how you configure ngrok, you may need to change the port used by the app)

10. Install the new draft version of your app into your Weebly Developer Test Site, and view the logs in your console

11. You can generate webhook events by logging into and out of your [Weebly Developer Site](https://weebly.com) account.

12. Open your heroku app and if you see "It works!" the app is running.

## Manual Heroku Deployment

The server is intended to be deployed via Heroku, and will look to Heroku-flavored environment variables for specific keys.

For Heroku usage, after cloning this repository (and assuming you have the Heroku CLI installed):

```
heroku create
heroku config:set WEEBLY_CLIENT_ID=[your_apps_client_id]
heroku config:set WEEBLY_SECRET_KEY=[your_apps_secret_key]
git push heroku master
heroku ps:scale web=1
```

On receiving a webhook, the server will write to the `messages/messages.txt` file; this file is accessible by navigating to the default route on your heroku instance (`/`), and thus is accessible via `heroku open`.
