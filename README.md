dfmedia/wp-cli-term-rollup
==========================
This CLI package is for attaching all post objects to all of the ancestors for specific terms in specific taxonomies. Essentially so that a post can be "rolled up" into all of the desired terms ancestors.


Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing)

## Using

```bash
wp term rollup category all
# Performs a rollup for all posts connected to any term in the category taxonomy

wp term rollup location 10 11 12 --post_type=restaurant
# Performs a rollup for all posts within the restaurant post type that are attached to location terms with the ID 10, 11, or 12.
```

## Installing

Installing this package requires WP-CLI v1.1.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install dfmedia/wp-cli-term-rollup`.

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/dfmedia/wp-cli-term-rollup/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/dfmedia/wp-cli-term-rollup/issues/new) with the following:

1. What you were doing (e.g. "When I run `wp post list`").
2. What you saw (e.g. "I see a fatal about a class being undefined.").
3. What you expected to see (e.g. "I expected to see the list of posts.")

Include as much detail as you can, and clear steps to reproduce if possible.

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/dfmedia/wp-cli-term-rollup/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, please follow our guidelines for creating a pull request to make sure it's a pleasant experience:

1. Create a feature branch for each contribution.
2. Submit your pull request early for feedback.
3. Include functional tests with your changes. [Read the WP-CLI documentation](https://wp-cli.org/docs/pull-requests/#functional-tests) for an introduction.
4. Follow the [WordPress Coding Standards](http://make.wordpress.org/core/handbook/coding-standards/).


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
