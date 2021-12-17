# Quick start, using base + umbrella.

* Create a NEW repository, use lowercase if integrating with heroku

  https://github.com/new

```bash
REPO=base-bundle-demo 
git clone git@github.com:survos/$REPO.git && cd $REPO 
rm -f LICENSE && rm -f README.md && mv .git .. && symfony new --full . --no-git --version=5.4 && mv ../.git . && git checkout .
composer config extra.symfony.allow-contrib true
composer req webapp && yarn install && yarn encore dev
git commit -m "initial version with just symfony"


echo "DATABASE_URL=sqlite:///%kernel.project_dir%/var/data.db" > .env.local
sed -i "s|type: annotation|type: attribute|" config/packages/doctrine.yaml

bin/console make:user User --is-entity --identity-property-name=email --with-password -n
sed -i "s|# MAILER_DSN|MAILER_DSN|" .env

composer config minimum-stability dev
composer config prefer-stable true



```


composer require umbrella2/adminbundle -n

bin/console make:user User --is-entity --identity-property-name=email --with-password -n
sed -i "s|\`user\`|users|" src/Entity/User.php

sed -i "s|# MAILER_DSN=smtp://localhost|MAILER_DSN=null://null|" .env


echo "1,AppAuthenticator,SecurityController,/logout," | sed "s/,/\n/g"  | bin/console make:auth
sed -i "s|// For example.*;|return new RedirectResponse(\$this->urlGenerator->generate('app_homepage'));|" src/Security/AppAuthenticator.php
sed -i "s|throw new \\Exception\('TODO\: provide a valid redirect inside '\.__FILE__\);||" src/Security/AppAuthenticator.php

bin/console make:admin:home -n

bin/console d:s:update --dump-sql --force

bin/console make:controller AppController
sed -i "s|Route('/app', name: 'app')|Route('/', name: 'app_homepage')|" src/Controller/AppController.php

// @todo: fix `user` class when creating user



