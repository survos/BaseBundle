 BUT, to make things very clear, if you want to allow your users to login via Facebook, you do NOT need an OAuth server - you just need to create an authenticator that is able to use an "oauth client" to communicate with some other OAuth server (e.g. Facebook).

> Ideally I'd like to allow users to be able to register an account the traditional way via a registration form username/password, or be able to connect their facebook or google account and gain access to the site that way.

We do this here on SymfonyCasts, so I can give you some hints :). Basically, allowing login via username/password and also via OAuth (e.g. Facebook) are 2 totally independent "options" for authentication and work quite well together. You will have one authenticator that allows users to login via an email/password and a second authenticator that allows users to "login via Facebook". In https://github.com/knpunive..., we have a spot on the docs that shows what a Guard authenticator setup might look like to login with Facebook. You will follow:

* Step 3) Use the Client Service - https://github.com/knpunive... - but leave the the connectCheckAction method blank... because we will do that logic in the authenticator instead.

* Authenticating with Guard - https://github.com/knpunive...

The entire flow is this:

A) User clicks "Login with Facebook" on your site to go to /connect/facebook.
B) In that controller, we redirect to Facebook (see the linked docs - we show exactly how to do this). At this point, we have already configured the bundle so that Facebook knows to redirect the user back to /connect/facebook/check after success (the connect_facebook_check) route).
C) After the user approves your app, they are redirected back to /connect/facebook/check.
D) Your authenticator sees this URL, and makes an API request to fetch the User data, and then you either find the existing User in your database and return them from getUser() or create a new User, save them, and then redirect. All of this is shown in the example on that page.

And, it's a bit bigger, but I'd also recommend going through the OAuth tutorial - http://symfonycasts.com/scr... - then you will *really* understand how all of this works.