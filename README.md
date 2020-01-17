# dropbox-webapp
A feature-rich implementation of [kunalvarma05](https://github.com/kunalvarma05)'s unofficial [dropbox-php-sdk](https://github.com/kunalvarma05/dropbox-php-sdk).

## Use at your own risk:
This application is an example of using PHP to access Dropbox HTTP API. 
* It makes no attempt at security or data sanatization. 
* It should not be deployed to a public webserver. 
* Anyone with the application URL will have full access to the linked Dropbox.

This is not intended to be run as an application on your website for sharing Dropbox files.


However, if you have a small localized team, this example works well on localhost where only one person has dropbox account. 

Or, as in the wikimedia use case: makes great Large File Storage for project documentation examples.


### Installation

1. Navigate to the directory you want to install in.

    `$ cd ~/public_html/mysite/`

2. Clone this repository.

    `$ git clone https://github.com/gamesolids/dropbox-webapp.git`

3. Rename the directory to URL you want to see.

    `$ mv dropbox-webapp my_url`

4. Install [kunalvarma05](https://github.com/kunalvarma05)'s unofficial [dropbox-php-sdk](https://github.com/kunalvarma05/dropbox-php-sdk).

    `$ php composer require kunalvarma05/dropbox-php-sdk`

5. Create a `webapp.config.php` file.

	`$ nano webapp.config.php`

```
	<?php 
	namespace gamesolids;
	/* webapp.config.php */

	/* Store your Dropbox application credentials.
	 * Get these when you set up your app at dropbox.com/developer .
	 */
	 
	namespace gamesolids;

	$dropbox_config = array();

	$dropbox_config['app_key'] = " ";
	$dropbox_config['app_secret'] = " ";
	$dropbox_config['access_token'] = " ";

	?>

```
6. Add your app's key, secret, and access token in the config file.


### That's it!

You should now be able to navigate to the install URL in your browser and use the application.



