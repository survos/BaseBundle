# Quick Start


```bash
script ~/session.log
symfony ...
exit
bin/console make:docs-from-log (get commits, etc.)
````


Add the recipe repo, and until basebundle is ready for production, add the repo.  Umbrella Admin Bundle is required for BaseBundle 2.0, it is likely that BaseBundle 3 will use Tabler.

"/home/tac/survos/bundles/recipes/index.json",


```bash
REPO=bbtest
symfony new --webapp $REPO && cd $REPO && yarn install 
composer config minimum-stability beta
composer req api-platform/core:^2.7
composer req api-platform/core:^3.0

composer config extra.symfony.endpoint --json '["https://api.github.com/repos/survos/recipes/contents/index.json", "flex://defaults"]'
composer config minimum-stability beta
composer config prefer-stable true

composer config repositories.survos_base_bundle '{"type": "vcs", "url": "git@github.com:survos/BaseBundle.git"}'
composer config repositories.survos_bootstrap_bundle '{"type": "vcs", "url": "git@github.com:survos/BootstrapBundle.git"}'
composer config repositories.tacman_hello '{"type": "vcs", "url": "git@github.com:tacman/TacmanHelloBundle.git"}'
composer config repositories.survos_workflow '{"type": "vcs", "url": "git@github.com:survos/workflow-bundle.git"}'
composer config repositories.tacman_tree_tag_tag '{"type": "vcs", "url": "git@github.com:tacman/twig-tree-tag.git"}'

composer config repositories.survos_maker '{"type": "vcs", "url": "git@github.com:survos/AdminMakerBundle.git"}'
composer config repositories.survos_maker '{"type": "path", "url": "/home/tac/survos/bundles/maker-bundle"}'
composer req survos/maker-bundle:*@dev && git diff config

composer config repositories.survos_base_bundle '{"type": "path", "url": "/home/tac/survos/bundles/BaseBundle"}'
composer req survos/base-bundle:*@dev && git diff config

composer config repositories.ics_bundle '{"type": "vcs", "url": "git@github.com:tacman/IcsBundle.git"}'

composer req umbrella2/adminbundle
composer req symfony/workflow
composer req survos/maker-bundle --dev

composer req survos/base-bundle:*
sed -i "s|# MAILER_DSN|MAILER_DSN|" .env

//return new RedirectResponse($this->urlGenerator->generate('some_route'));
bin/console make:user --is-entity --identity-property-name=email --with-password User -n
echo "1,AppAuthenticator,,," | sed "s/,/\n/g"  | bin/console make:auth

sed  -i "s|some_route|app_homepage|" src/Security/AppAuthenticator.php 
sed  -i "s|//return|return|" src/Security/AppAuthenticator.php 
sed  -i "s|throw new|//throw new|" src/Security/AppAuthenticator.php 

bin/console survos:make:menu AdminMenu (maybe just copy during recipe)?

```



## Create on github

```bash
```




## UX Datatable (work in progress)
composer config repositories.ux-datatable '{"type": "vcs", "url": "git@github.com:tacman/ux-datatable.git"}'
composer req tacman/ux-datatable



How KnpMenu works.

The MenuBuilderService in Survos\BaseBundle\Menu is registered because it's configured in services.yaml in this bundle.

```yaml
  Survos\BaseBundle\Menu\MenuBuilder:
    class: Survos\BaseBundle\Menu\MenuBuilder
    arguments:
      - "@knp_menu.factory"
      - "@event_dispatcher" 
    tags:
      - { name: knp_menu.menu_builder, method: createSidebarMenu, alias: survos_sidebar_menu }
      - { name: knp_menu.menu_builder, method: createPageMenu, alias: survos_page_menu }
```

This means that whenever knp_menu_get is called with the alias (e.g. survos_sidebar_menu), MenuBuilder.php creates a root element and emits an event, which SidebarMenuSubscriber (in the application) picks up.

Then we can build the menus.



@TODO:

* Extend from Umbrella
* Add DataTableBundle (ux component)
* Add #[RouteParameter] to Entity Properties.
* Make ParamConverter listen via Attributes
* Add LandingBundle (make:landing)
* Symfony 5.4 or 6

# Survos Admin Bundle

A moderately-opinionated bundle that provides a quick way to get up and running with Symfony.  
In particular, it sets up and uses the following:

* AdminLTE4 (Bootstrap 5)
* Knp Menu for sidebar and top nagivation
* webpack encore 
* optional jQuery (for js-tree and datatables)

## Assumptions

While the following can be disabled, by default the bundle assumes you want the following

* Authentication
* A user and an admin role
* Users
* An API (for Vue, DataTables)
* Bootstrap 5, icons
* Deployable on Heroku

## Process

Go to ... and fill out the form with what you want.  Run the script to create the Symfony shell.

```

Then in app.js

```javascript
require('adminkit/static/js/app');
require('../css/app.scss');
```

and app.scss

```scss
@import "~adminkit/static/css/app.css";
```


Change app.js:

```javascript
require('@popperjs/core');
require('bootstrap');
require('Hinclude/hinclude');
require('./css/app.scss');

```

```scss
@import "~bootstrap/dist/css/bootstrap.min.css";
@import "../../public/bundles/survosbase/volt-dist/css/volt.css";
```

### Goals

This bundle was created originally to isolate issues with other bundles and to get data on a website as quickly and painlessly as possible.  

## Some themes worth checking out

https://github.com/xriley/portal-theme-bs5 (check license though)
https://github.com/zuramai/voler 

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
rm -f LICENSE && rm -f README.md && mv .git .. && symfony new --full . --no-git --version=5.4 && mv ../.git . && git checkout .
composer config extra.symfony.allow-contrib true
composer req webapp && yarn install && yarn encore dev
```     
        
* Create the project on heroku, after logging in.  Optionally create database.

DBNAME=test
echo "DATABASE_URL=postgresql://main:main@127.0.0.1:5432/$DBNAME" > .env.local

  
OR if you're using Sqlite.

```bash
heroku create $REPO
heroku addons:create heroku-postgresql:hobby-dev
echo "DATABASE_URL=$(heroku config:get DATABASE_URL)" > .env.heroku.local
# Without heroku, use sqlite (or setup MySQL)
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local

```

We always want some security, so certain routes can be secured. Create a User entity, and then a LoginFormAuthenticator.  Tweak AppAuthenciator to return to home after a successful login.  Set the MAILER_URL to the default.

```bash
bin/console make:user User --is-entity --identity-property-name=email --with-password -n
#sed -i "s|public function getEmail| public function getUsername() { return \$this->getEmail(); }\n\n public function getEmail|" src/Entity/User.php

sed -i "s|# MAILER_DSN|MAILER_DSN|" .env


echo "1,AppAuthenticator,SecurityController,/logout," | sed "s/,/\n/g"  | bin/console make:auth
sed -i "s|// For example.*;|return new RedirectResponse(\$this->urlGenerator->generate('app_homepage'));|" src/Security/AppAuthenticator.php 
sed -i "s|throw new \\Exception\('TODO\: provide a valid redirect inside '\.__FILE__\);||" src/Security/AppAuthenticator.php 


```
    
# Optional, since SurvosBaseBundle has this already, formatted for mobile

    bin/console make:registration-form
    
# Now install the Survos BaseBundle

```bash
composer config minimum-stability dev
composer config minimum-stability beta
composer config prefer-stable true

composer config repositories.tacman_hello '{"type": "path", "url": "/home/tac/survos/bundles/TacmanHelloBundle"}'

composer config repositories.html_prettify '{"type": "path", "url": "/home/tac/survos/bundles/HtmlPrettifyBundle"}'
COMPOSER_DISABLE_NETWORK=1 composer req survos/html-prettify-bundle:*@dev

composer config repositories.barcode '{"type": "path", "url": "/home/tac/survos/bundles/BarcodeBundle"}'
composer config repositories.survos_workflow '{"type": "path", "url": "/home/tac/survos/bundles/WorkflowBundle"}'

composer config repositories.survos_maker '{"type": "path", "url": "/home/tac/survos/bundles/maker-bundle"}'
composer req survos/maker-bundle:*@dev && git diff config

# composer req survos/barcode-bundle:*@dev
git checkout . && composer req survos/barcode-bundle:*@dev && git diff config

composer config repositories.survos_tree '{"type": "path", "url": "/home/tac/survos/bundles/SurvosTreeBundle"}'
composer req survos/tree-bundle:*@dev && git diff config

composer config repositories.foo '{"type": "path", "url": "/home/tac/survos/bundles/FooBundle"}'
composer req survos/foo-bundle:*@dev

composer config repositories.barcode '{"type": "vcs", "url": "git@github.com:survos/BarcodeBundle"}'

composer config repositories.barcode '{"type": "path", "url": "/home/tac/survos/bundles/BarcodeBundle"}'
composer req survos/barcode-bundle:*@dev && git diff config
composer req survos/barcode-bundle && git diff config


#git checkout . && composer req knpuniversity/lorem-ipsum-bundle:*@dev && git diff config



composer config repositories.lorum '{"type": "vcs", "url": "git@github.com:tacman/lorem-ipsum-bundle.git"}'

composer config repositories.lorum '{"type": "path", "url": "/home/tac/survos/bundles/lorem-ipsum-bundle"}'
git checkout . && composer req knpuniversity/lorem-ipsum-bundle:*@dev && git diff config


composer config repositories.knp_dictionary '{"type": "vcs", "url": "git@github.com:tacman/DictionaryBundle.git"}'

composer config extra.symfony.endpoint --json '["/home/tac/survos/bundles/recipes/index.json","https://api.github.com/repos/survos/recipes/contents/index.json", "flex://defaults"]'

composer config repositories.knp_markdown '{"type": "vcs", "url": "git@github.com:tacman/KnpMarkdownBundle.git"}'
composer req knplabs/knp-markdown-bundle:dev-symfony6

composer config repositories.survos_grid_bundle '{"type": "vcs", "url": "git@github.com:survos/SurvosGridBundle.git"}'
composer config repositories.survos_base_bundle '{"type": "vcs", "url": "git@github.com:survos/BaseBundle.git"}'
composer config repositories.ux-datatable '{"type": "vcs", "url": "git@github.com:tacman/ux-datatable.git"}'
composer req tacman/ux-datatable

composer require umbrella2/adminbundle
php bin/console make:admin:home

composer config repositories.cs_fixer '{"type": "vcs", "url": "git@github.com:tacman/PHP-CS-Fixer.git"}'
composer config repositories.survos_workflow '{"type": "vcs", "url": "git@github.com:survos/workflow-bundle.git"}'
composer config repositories.tabler '{"type": "vcs", "url": "git@github.com:survos/TablerBundle.git"}'
composer req survos/tabler-bundle:dev-tac

composer req survos/base-bundle:"^2.0.3"

composer require symfony/webpack-encore-bundle
yarn install
yarn add sass-loader@^11.0.0 sass --dev
yarn add https://github.com/survos/adminkit.git

#echo '@import "~bootstrap/dist/css/bootstrap.min.css";' > assets/styles/app.scss
#echo '@import "../../public/bundles/survosbase/volt-dist/css/volt.css";' >>assets/styles/app.scss
## FIRST, initialize SurvosBase, which creates app.scss.  Then fix webpack.

sed -i "s|//.enableSassLoader()|.enableSassLoader()|" webpack.config.js
sed -i "s|import './styles/app.css';|import './styles/app.scss';|" assets/js/app.js

yarn install

yarn add "@symfony/webpack-encore@^1.0.0"
yarn add "@symfony/stimulus-bridge@^2.0.0"
yarn add bootstrap@next

yarn add datatables.net-bs5 datatables.net-buttons-bs5 datatables.net-scroller datatables.net-scroller-bs5 datatables.net-select-bs5 datatables.net-searchpanes datatables.net-searchpanes-bs5 datatables.net-colreorder datatables.net-colreorder-bs5

```


  # Survos Dev only
composer config repositories.knp_dictionary '{"type": "path", "url": "/home/tac/survos/bundles/DictionaryBundle"}'
composer config repositories.ux_datatables '{"type": "path", "url": "/home/tac/survos/bundles/ux-datatable"}'
composer config repositories.survos_workflow '{"type": "path", "url": "/home/tac/survos/bundles/WorkflowBundle/"}'

composer config repositories.tacman_twig_tree '{"type": "path", "url": "/home/tac/survos/bundles/twig-tree-tag/"}'
COMPOSER_DISABLE_NETWORK=1 composer req jordanlev/twig-tree-tag:*@dev



composer config repositories.survos_workflow '{"type": "vcs", "url": "git@github.com:survos/workflow-bundle.git"}'
composer config repositories.kickass_subtitles '{"type": "vcs", "url": "git@github.com:tacman/open-subtitles.git"}'


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
    composer require knplabs/knp-menu-bundle
    bin/console make:subscriber KnpMenuSubscriber "Survos\BaseBundle\Event\KnpMenuEvent"
    
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

    heroku create $projectName 
    heroku buildpacks:add heroku/php
    heroku buildpacks:add heroku/nodejs
    
    echo "web:  vendor/bin/heroku-php-nginx -C heroku-nginx.conf  -F fpm_custom.conf public/" > Procfile

    heroku buildpacks:add heroku/nodejs
    heroku buildpacks:add --index 2 heroku/nodejs

    
    composer config --unset repositories.survosbase && composer update
    git commit -m "unset survosbase" . && git push heroku master

https://devcenter.heroku.com/articles/deploying-symfony4
bin/console survos:setup-heroku



   ## alternatives

https://github.com/xriley/portal-theme-bs5
    

