# php-webhook-client

This is a simple webhook listener client written in PHP as example server-side code for the Weebly Developer Platform. More information about the platform can be found at https://dev.weebly.com.

The server does two main things:

1. Acts as a server to facilitate an OAuth handshake between Weebly and your app
2. Provides an endpoint for webhook-enabled apps

### Usage

The server is intended to be deployed via Heroku, and will look to Heroku-flavored environment variables for specific keys.

For Heroku usage, after cloning this repository (and assuming you have the Heroku CLI installed):

```
heroku create
heroku config:set WEEBLY_CLIENT_ID=[your_apps_client_id]
heroku config:set WEEBLY_SECRET_KEY=[your_apps_secret_key]
git push heroku master
heroku ps:scale web=1
```

For non-heroku usage, you can define your keys either within the code, or via other environment variables. 

### OAuth

This server has two main endpoints for OAuth; `/oauth/phase-one` and `/oauth/phase-two`. These correspond to the two endpoints as defined here: https://dev.weebly.com/configure-oauth.html. In order to enable OAuth on your app, you will need to define the following two lines in your `manifest.json`:

```json
{
	"callback_url": "[SERVER_INSTNACE]/oauth/phase-one",
	"oauth_final_destination": "editor"
}
```

After completing the OAuth flow, an access token will be dumped to the console. You can access the heroku logs via `heroku logs --tail`.

### Webhooks

By default, this server sets the scope of the OAuth app to only webhooks, and comes with a POST endpoint at `/webhooks/callback`. To properly set up webhook events, add the following to your `manifest.json`: 

```json
{
	"callback_url": "[SERVER_INSTANCE]/webhooks/callback",
	"events": [

	]
}
```

The `events` key is an array of events; you can pick and choose events from the documentation here: https://dev.weebly.com/use-webhooks.html. 

On receiving a webhook, the server will write to the `messages/messages.txt` file; this file is accessible by navigating to the default route on your heroku instance (`/`), and thus is accessible via `heroku open`.
 
### Sample Manifest

We have also included a sample `manifest.json` file in the root directory of this folder. If you wish to do simple tests of the webhook system and the OAuth handshake, follow the following steps.

1. Create a new app within the Weebly Developer Admin
2. Edit the `manifest.json` file to include your app's CLIENT_ID and ROOT_URL (which should be the same as your Heroku deploy's URL)
3. Zip up the `manifest.json` file, and upload it as a new version of the newly created app

If you wish to implement OAuth and webhooks to your existing app, include the bits of JSON described in the two sections above.

The sample `manifest.json` file has every webhook currently available, which means that a webhook will get sent for every applicable event. To tweak this, remove events as you please.
