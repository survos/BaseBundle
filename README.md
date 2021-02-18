
# base-bundle

A moderately-opinionated bundle that provides a quick way to get up and running with Symfony.  
In particular, it sets up and uses the following:

* AdminLTE (https://adminlte.io/docs/3.0/index.html)
* AdminLTE Bundle
* Knp Menu for sidebar and top nagivation
* webpack encore 

### Upgrading to Bootstrap5 and Volt

    use Survos\BaseBundle\Event\KnpMenuEvent; # (instead of KevinPabst)
    yarn remove jquery
    yarn remove popper.js
    yarn add @popperjs/core
    yarn add Hinclude
    yarn remove admin-lte
    yarn upgrade sass-loader@10
    yarn remove bootstrap && yarn install bootstrap@next
    yarn upgrade @symfony/webpack-encore@1.1
    


Change app.js:

```javascript
require('@popperjs/core');
require('bootstrap');
require('Hinclude/hinclude');
require('../css/app.scss');

```


### Goals

This bundle was created originally to isolate issues with other bundles and to get data on a website as quickly and painlessly as possible.  


### Requirements

* composer
* PHP 7.2+
* yarn
* Symfony CLI (for running a local server, creating project, etc.)

### Create github Project

* Create a NEW repository, use lowercase if integrating with heroku

    https://github.com/new

on github.com with no files (no README or license), clone it to some directory and go there.

     REPO=base-bundle-demo 
     git clone git@github.com:survos/$REPO.git && cd $REPO 
     
* Create the Symfony Skeleton WITHOUT a git repo, then ADD the repo.  Allow recipes

```bash
mv .git .. && symfony new --full . --no-git  && mv ../.git .
composer config extra.symfony.allow-contrib true
```     
        
* Create the project on heroku, after logging in.  Optionally create database.

  
OR if you're using Sqlite.

```bash
heroku create $REPO
heroku addons:create heroku-postgresql:hobby-dev
echo "DATABASE_URL=$(heroku config:get DATABASE_URL)" > .env.heroku
# Without heroku, use sqlite (or setup MySQL)
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
```

We always want some security, so certain routes can be secured. Create a User entity, and then a LoginFormAuthenticator.  Tweak AppAuthenciator to return to home after a successful login.  Set the MAILER_URL to the default.

```bash
bin/console make:user User --is-entity --identity-property-name=email --with-password -n
echo "1,AppAuthenticator,SecurityController,/logout," | sed "s/,/\n/g"  | bin/console make:auth
sed -i "s|// For example.*;|return new RedirectResponse(\$this->urlGenerator->generate('app_homepage'));|" src/Security/AppAuthenticator.php 
sed -i "s|throw new \\Exception\('TODO\: provide a valid redirect inside '\.__FILE__\);||" src/Security/AppAuthenticator.php 
sed -i "s|# MAILER_DSN|MAILER_DSN|" .env
```
    
# Optional, since SurvosBaseBundle has this already, formatted for mobile

    bin/console make:registration-form
    
# Now install the Survos BaseBundle

```bash
composer config minimum-stability dev
composer config prefer-stable true

composer config repositories.admin_lte_bundle '{"type": "vcs", "url": "git@github.com:survos/AdminLTEBundle.git"}'
composer config repositories.survos_base_bundle '{"type": "vcs", "url": "git@github.com:survos/BaseBundle.git"}'

composer req kevinpapst/adminlte-bundle:dev-adminlte-v3 survos/base-bundle
composer require "almasaeed2010/adminlte=~3.0"
```


  # Survos Dev only
    composer config repositories.survos_base_bundle '{"type": "path", "url": "../Survos/BaseBundle"}'
    composer config repositories.geonames '{"type": "path", "url": "../Survos/geonames-bundle"}'
    composer config repositories.phpspreadsheet '{"type": "path", "url": "../Survos/phpspreadsheet-bundle"}'
    
    
## @TODO: recipes!


    
    composer config repositories.multisearch '{"type": "vcs", "url": "git@github.com:tacman/PetkoparaMultiSearchBundle.git"}'
    
    repositories.captcha '{"type": "vcs", "url": "git@github.com:cyrilverloop/symfony-captcha-bundle.git"}'

    composer config repositories.git-survosbase '{"type": "vcs", "url": "https://github.com/survos/BaseBundle.git"}'

    composer config repositories.git-geonames '{"type": "vcs", "url": "https://github.com/survos/geonames-bundle.git"}'

    composer config repositories.flowdemo '{"type": "path", "url": "../Survos/../CraueFormFlowDemoBundle"}'

    
    composer config repositories.social_post_bundle '{"type": "path", "url": "../Survos/social-post-bundle"}'

    composer config repositories.social_post_bundle '{"type": "vcs", "url": "https://github.com/tacman/social-post-bundle"}'

    # this is needed because it creates MAILER_DSN, which isn't created otherwise
    # composer req mail
    composer req knplabs/knp-menu-bundle:"^3.0@dev"

    composer req survos/base-bundle:"*@dev"
    phpstorm .env

OR
## Step 1: Initialize Yarn Packages via Survos Init


    # creates survos_base.yaml (a recipe would be nicer!)    
    bin/console survos:init
    
    # edit .env and set MAILER_URL
    
# Ugh, still doesn't work, needs a base menu    

    # introspection, creates menus, looks for entities, easyadmin, etc.
    bin/console survos:configure
     
    # symfony run -d yarn encore dev --watch

### Integrating Facebook and other OAuth

Go to https://github.com/knpuniversity/oauth2-client-bundle#step-1-download-the-client-library

e.g. 

    composer require league/oauth2-facebook

The create an app and enable login: https://developers.facebook.com/apps/

Need a config script that asks for the ID and sets it in .env.local (or Heroku, etc.)
    
https://developers.facebook.com/apps/558324821626788/settings/basic/

### Install and Configure UserBundle (optional)

See [docs/recommended_bundles]


#### If developing BaseBundle

    composer config repositories.survosbase '{"type": "path", "url": "../Survos/BaseBundle"}'
    composer req survos/base-bundle:"*@dev"

#### Normal installation

Install the bundle, then go through the setup to add and configure the tools.

    composer req survos/base-bundle
    
    yarn install 
    
    xterm -e "yarn run encore dev-server" &
    
    composer req "kevinpapst/adminlte-bundle"
    bin/console make:subscriber KnpMenuSubscriber "KevinPapst\AdminLTEBundle\Event\KnpMenuEvent"
    
    bin/console survos:init
    
    

    bin/console survos:config --no-interaction
    bin/console doctrine:schema:update --force
    
#### survos:init

First time setup, downloads jquery, bootstrap, etc.
Also _modifies_ some yaml files, and creates the first menu.  

```yaml
# config/packages/admin_lte.yaml
admin_lte:
    knp_menu:
        enable: true

    routes:
        adminlte_welcome: app_homepage
        adminlte_login: app_login
        adminlte_profile: app_profile
```

@todo: Generate this Controller and templates?

```yaml
# config/routes/survos_base.yaml
survos_base: {path: /, controller: 'Survos\BaseBundle\Controller\BaseController::base'}
# app_homepage: {path: /, controller: 'Survos\BaseBundle\Controller\LandingController::base'}
app_logo: {path: /logo, controller: 'Survos\BaseBundle\Controller\BaseController::logo'}
app_profile: {path: /profile, controller: 'Survos\BaseBundle\Controller\BaseController::profile'}
# profile: {path: /profile, controller: 'Survos\BaseBundle\Controller\LandingController::profile'}
# logout: {path: /logout, controller: 'Survos\BaseBundle\Controller\LandingController::logout'}
# required if app_profile is used, since you can change the password from the profile
app_change_password: {path: /change-password, controller: 'Survos\BaseBundle\Controller\BaseController::changePassword'}
```


{% extends '@SurvosBaseBundle/layout/default-layout.html.twig' %}
{% block page_content %}
{{ block('body') }}
{% endblock %}

{% block logo_mini %}<b>KPA</b>{% endblock %}
{% block logo_large '<b>KPA</b> Admin' %}

{% block page_title 'KPA Admin' %}
{% block page_subtitle 'Songs and Music!' %}

### 2021 Goal: Remove jQuery

https://tobiasahlin.com/blog/move-from-jquery-to-vanilla-javascript/

use vue or react instead.
https://www.smashingmagazine.com/2020/07/desktop-apps-electron-vue-javascript/k

#### Now install some bundles!
     
    See the details at [Recommended Bundles](docs/recommended-bundles.md)

If you chosen to integrate the userbundle, update the schema and add an admin    
    
    bin/console doctrine:schema:update --force

    symfony server:start --no-tls
    
When finished, the application will have a basic base page with top navigation, optionally including login/registration pages.  Logged in users with ROLE_ADMIN will also (optionally) have links to easyadmin and api-platform.  

### Api Platform

@todo: put this in a survos:setup command.

* Expose the API routes (for jsRoutingBundle), and 

```yaml
# config/routes/api_platform.yaml
api_platform:
    resource: .
    type: api_platform
    prefix: /api
    options:
        expose: true
```

Create resources.yaml to store the configuration
```yaml
# api/config/api_platform/resources.yaml
App\Entity\User: ~
App\Entity\Location:
  shortName: 'Location'                   # optional
  description: 'A place within a building where inventory item is physically located.' # optional
  attributes:                          # optional
    pagination_items_per_page: 30   # optional
    normalization_context:
      groups: ['jstree']
    denormalization_context:
      groups: ['jstree']
```

Add the resources.yaml directory to the mapping paths

```yaml
# config/packages/api_platform.yaml
api_platform:
    mapping:
        paths:
            - '%kernel.project_dir%/src/Entity'
            - '%kernel.project_dir%/config/api_platform' # yaml or xml directory configuration]
    patch_formats:
        json: ['application/merge-patch+json']
    swagger:
        versions: [3]
```

Configure the serializer (may need to create the directory)

```yaml
# config/serializer/serialization.yaml
App\Entity\User:
  attributes:
    id:
      groups: ['Default']
    email:
      groups: ['Default']

App\Entity\Song:
  attributes:
    title:
      groups: ['Default']
```

### Customizing the bundle

### Deploy to heroku

    heroku create $projectName --buildpack heroku/php
    heroku buildpacks:add heroku/nodejs
    
    echo "web:  vendor/bin/heroku-php-nginx -C heroku-nginx.conf  -F fpm_custom.conf public/" > Procfile

    heroku buildpacks:add heroku/nodejs
    heroku buildpacks:add --index 2 heroku/nodejs

    
    composer config --unset repositories.survosbase && composer update
    git commit -m "unset survosbase" . && git push heroku master

https://devcenter.heroku.com/articles/deploying-symfony4
bin/console survos:setup-heroku



   
    

