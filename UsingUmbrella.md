# Quick start, using base + umbrella.

* Create a NEW repository, use lowercase if integrating with heroku

  https://github.com/new

```bash
REPO=base-bundle-demo 
REPO=bard 

# from github...
git clone git@github.com:survos/$REPO.git && cd $REPO 
rm -f LICENSE && rm -f README.md && mv .git .. && symfony new --full . --no-git --version=5.4 && mv ../.git . && git checkout .


# locally 
# tweak the base to use the Tabler layout

# Add some routes to AppController, then add those routes to the menu

symfony new --full $REPO --version=5.4 && cd $REPO 
sed -i "s|7\.2\.5|8.0|" composer.json
composer config extra.symfony.allow-contrib true
composer config extra.symfony.endpoint --json '[ "https://api.github.com/repos/survos/recipes/contents/index.json","flex://defaults"]'
composer req webapp && yarn install && yarn encore dev
git add . && git commit -m "initial version with just symfony"


echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
sed -i "s|type: annotation|type: attribute|" config/packages/doctrine.yaml

bin/console make:user User --is-entity --identity-property-name=email --with-password -n
sed -i "s|\`user\`|users|" src/Entity/User.php
sed -i "s|# MAILER_DSN=smtp://localhost|MAILER_DSN=null://null|" .env

composer config minimum-stability dev
composer config prefer-stable true

git add . && git commit -m "Add a User entity"

```

# need to install SurvosBaseBundle first, so we have a route to redirect to.
composer config repositories.survos_base_bundle '{"type": "vcs", "url": "git@github.com:survos/BaseBundle.git"}'
composer req knplabs/knp-markdown-bundle symfonycasts/verify-email-bundle survos/base-bundle:"*@dev"
composer req kevinpapst/tabler-bundle
cp vendor/kevinpapst/tabler-bundle/config/packages/tabler.yaml config/packages/.


composer recipe:install survos/base-bundle  --force
git add . && git commit -m "After installing survos-base"

bin/console d:s:update --dump-sql --force

bin/console make:controller AppController
sed -i "s|Route('/app', name: 'app')|Route('/', name: 'app_homepage')|" src/Controller/AppController.php

echo "1,AppAuthenticator,SecurityController,/logout," | sed "s/,/\n/g"  | bin/console make:auth
echo "yes,yes,yes,tacman@gmail.com,Test Email,yes," | sed "s/,/\n/g"  | bin/console make:registration-form
sed -i "s|//return|return|" src/Security/AppAuthenticator.php
sed -i "s|some_route|app_homepage|" src/Security/AppAuthenticator.php


Add logout
            logout:
                path: app_logout




composer require umbrella2/adminbundle umbrella2/corebundle -n
bin/console make:admin:home -n





// @todo: fix `user` class when creating user



