# Raptcha

Raptcha is an image-based humanity-challenge which uses randomly rotated images.

### Demo
[https://raptcha.zealmayfield.com/example/](https://raptcha.zealmayfield.com/example/)

## Test Locally (Ubuntu linux instructions)
* Clone this repo
* From the `raptcha` directory, run:
  * `composer install`
  * `composer setup` (enter your password when asked)
  * `composer serve`
* Visit [http://localhost:8080/add_image](http://localhost:8080/add_image)
* Select an image and rotate it to the optimal vertical alignment (this works best with things like landscape with visible horizon line.)
* Save the image.

## Run the challenge example
* In the `raptcha` directory, run: `cd example && php -S localhost:3000`
* Visit [http://localhost:3000](http://localhost:3000)
* Enter an E-mail address (No E-mail will be sent. The example merely illustrates the method you would use to do so.)
* Move the slider to rotate the image to the correct orientation.
* Click the button labelled 'Okay, that looks right!'

Challenge logic can be found in `/Challenge.php`

## Usage
`/example/raptcha.js` is the end-user JS client module.
On any page with a form, simply:
* Add `class="raptcha"` to the form you want to include the challege.
* Import the module.
* Call the `raptcha` function, passing a success callback as the sole argument. The function inserts a challenge into the form, just before the submit button. The callback will execute when the user completes the challenge.

See `/example/index.html` for example code.
