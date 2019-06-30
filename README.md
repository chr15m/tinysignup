# DO NOT RELEASE UNTIL CSV IS AUTHED

Newsletter signup in a single script.

## Install & configure

 * Copy `tinysignup.php`, `tinysignup-config-data.php` and `tinysignup.js` up onto your server.
 * Open the tinysignup.php script in the browser to create the config file `tinysignup-config-data.php`.
 * Edit `tinysignup-config-data.php` to set up your own list name.

```html
<script src="/path/to/tinysignup.php?list=LISTNAME" data-message="" data-div="" data-form=""></script>
```

If you want to customise the message which is shown use the `data-message` attribute:

```html
<script src="/path/to/tinysignup.php?list=LISTNAME" data-message="Sign up to my cool list!"></script>
```

If you want to completely customise the form which is shown, use the `data-form` and `data-div` attributes:

```html
<div id="signup" class="tinysignup">
  <form id="signup-form" method="post" action="/path/to/tinysignup.php">
    <input type="email" name="email" id="email" placeholder="Email Address" />
    <input type="hidden" name="list" id="list" value="invention" />
    <input type="submit" value="Sign Up" />
  </form>
</div>
<script src="tinysignup/tinysignup.php?list=default" data-div="signup" data-form="signup-form"></script>
```

## Use

TODO: note about where signups are stored.

 * Download the CSV like this: `https://YOUR-SERVER/tinysignup.php?csv=invention`
 * Perform mail merge.
 * X-List-unsubsribe header info.

