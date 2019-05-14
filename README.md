Single-script newsletter signup.

 * CSV storage.

## Install & configure



## Use

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

## Mail

## Unsubscribe
