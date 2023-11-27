# Contao 4 Managed Edition Skeleton

This is a template for projects based on [Contao 5 Managed Edition](https://github.com/contao/managed-edition),
that simply has some additional tools preconfigured.

## What's added?

[Webpack Encore](https://github.com/symfony/webpack-encore) and [Foundation](https://get.foundation/) are pre-set
for building frontend (don't forget to run `npm install`).

Accordingly, [ameotoko/contao-encore-bridge](https://github.com/ameotoko/contao-encore-bridge) is added, 
which only does two things:

1. It requires and enables [symfony/webpack-encore-bundle](https://github.com/symfony/webpack-encore-bundle)
2. It adds two helper methods, allowing you to use `encoreEntryScriptTags()` and `encoreEntryLinkTags()`
in your `.html5` templates as well as in Twig.

Additionally, [Deployer](https://deployer.org/) is added to Composer's `require-dev` requirements, 
along with a sensible configuration example (see `deploy.php`). It also includes two helper tasks
(`database:release` and `database:retrieve`) to help with synchronizing your `dev` and `production` databases
(thanks to folks at [terminal42](https://github.com/terminal42)).
