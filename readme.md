## Webiik boilerplate
Use this Boilerplate(skeleton) to quickly set up easily customisable website with:

- multilingual support
- secured advanced user accounts
- social sign-up and login
- Twig template engine with email templates
- production / development environment settings
- optional advanced logging of missing routes, missing translations and runtime errors

###### Installation:
1. `composer create-project webiik/boilerplate [your-app-dir]`
2. Inside `[your-app-dir]/private` run `composer install`
3. Create MySQL database called `webiik` and create tables within this database with queries located in file `[your-app-dir]/private/vendor/webiik/webiik/src/WebiikFW/migration/db.sql`
4. Configure your app in `[your-app-dir]/private/app/config.php`. You can also create `config.local.php` for local configuration.

[Documentation [to be done]]()
[Live example [to be done]]()

## Security vulnerabilities
If you discover a security vulnerability within Webiik or this boilerplate, please send me an email at jiri@mihal.me.

## License
Copyright (c) 2017 Jiri Mihal
[MIT license](http://opensource.org/licenses/MIT)