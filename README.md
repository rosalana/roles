# Rosalana Roles

This package is a part of the Rosalana eco-system. It provides a way to manage user roles and permissions across the Rosalana applications, allowing for a unified and consistent approach to user management.

> **Note:** This package is a extension of the [Rosalana Core](https://packagist.org/packages/rosalana/core) package.

## Table of Contents

- [Installation](#installation)
- [Configuration](#configuration)
- [Features](#features)
- [May Show in the Future](#may-show-in-the-future)
- [License](#license)

## Installation

To install the `rosalana/roles` package, you must first have the `rosalana/core` package installed. If you haven't installed it yet, please refer to the `rosalana/core` documentation.

After installing the `rosalana/core` package, use the `rosalana:add` command from the Rosalana CLI and select `rosalana/roles` from the list:

```bash
php artisan rosalana:add
```

After installing the package, you should publish its assets using the following command:

```bash
php artisan rosalana:publish
```

You can specify which files to publish. Publishing **the configuration files is required** to set up the package properly. Other files are optional and can be published as needed. However, it is recommended to publish all files to take full advantage of the package features.

## Configuration

After publishing the package, you will find a `rosalana.php` configuration file in the `config` directory of your Laravel application. You can customize these options according to your needs.

This file will grow over time as you add more Rosalana packages to your application. Each package contributes its own configuration section. The `rosalana.php` file serves as the central configuration hub for all Rosalana packages.

`rosalana/roles` package provides configuration options for:

- 

## Features


## May Show in the Future

- 

Stay tuned — we're actively shaping the foundation of the Rosalana ecosystem.

## License

Rosalana Roles is open-source under the [MIT license](/LICENCE), allowing you to freely use, modify, and distribute it with minimal restrictions.

You may not be able to use our systems but you can use our code to build your own.

For details on how to contribute or how the Rosalana ecosystem is maintained, please refer to each repository’s individual guidelines.

**Questions or feedback?**

Feel free to open an issue or contribute with a pull request. Happy coding with Rosalana!
