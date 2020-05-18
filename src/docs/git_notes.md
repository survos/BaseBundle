# Git Notes

## Using a Fork of a Bundle

Symfony 5 is out!  Alas, not all bundes have been updated, here's the basic steps to using a fork and submitting a PR. Also, see https://blog.scottlowe.org/2015/01/27/using-fork-branch-git-workflow/

Go to github and fork the project.  Clone it and add the upstream remote:

    git clone git@github.com:tacman/oauth2-client-bundle.git
    git remote add upstream https://github.com/knpuniversity/oauth2-client-bundle.git
    
    git fetch upstream
    git merge upstream/master
    
Switch to a new branch, e.g.

    git checkout -b symfony5
    
Make the changes, then add the local repo to your projects.

    composer config repositories.oauth2-client-bundle '{"type": "path", "url": "../Survos/oauth2-client-bundle"}'


    
    