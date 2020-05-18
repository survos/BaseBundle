# Recommended Bundles

Because the BaseBundle detects which bundles are installed to facilitate configuration, it is recommended to install these bundles before running the base bundle setup.

If you want to install everything that's recommended, run the following

    composer req maker --dev
    composer req messenger msgphp/user-bundle
    
    
    
## MsgPhp/UserBundle

FOSUserBundle isn't really supported very well with Symfony 4.  Use this instead, it's a fast way to configure user authentication that follows Symfony best practices, and has no deprecation warnings so it will work with Symfony 5.

    composer req maker --dev
    composer req messenger msgphp/user-bundle

    bin/console make:user:msgphp -n

    
    