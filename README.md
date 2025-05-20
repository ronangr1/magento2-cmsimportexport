# Module for Magento 2

[![Latest Stable Version](https://img.shields.io/packagist/v/ronangr1/module-cmsimportexport.svg?style=flat-square)](https://packagist.org/packages/ronangr1/module-cmsimportexport)
[![License: MIT](https://img.shields.io/github/license/ronangr1/m2-cmsimportexport.svg?style=flat-square)](./LICENSE)
[![Packagist](https://img.shields.io/packagist/dt/ronangr1/module-cmsimportexport.svg?style=flat-square)](https://packagist.org/packages/ronangr1/module-cmsimportexport/stats)
[![Packagist](https://img.shields.io/packagist/dm/ronangr1/module-cmsimportexport.svg?style=flat-square)](https://packagist.org/packages/ronangr1/module-cmsimportexport/stats)

This module enables you to import and export your CMS content on Magento 2

- [Setup](#setup)
    - [Composer installation](#composer-installation)
    - [Setup the module](#setup-the-module)
- [Documentation](#documentation)
- [Support](#support)
- [Authors](#authors)
- [License](#license)

## Setup

Magento 2 Open Source or Commerce edition is required.

This module is compatible with the latest Magento version (2.4.8).

### Install the module package with Composer

`composer require ronangr1/module-cmsimportexport`

### Launch standard Magento commands

```
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f --area="adminhtml"
```

## Documentation

You can allow or disallow page (or block) overwriting by setting configuration in `Store > Configuration > Ronangr1 > CmsImportExport > General > Allow Overwrite`.

You can choose to export media by setting configuration in `Store > Configuration > Ronangr1 > CmsImportExport > Media > Allow Media Download`.

## Support

Raise a new [request](https://github.com/ronangr1/m2-cmsimportexport/issues) to the issue tracker.

## Authors

- **ronangr1** - *Maintainer* - [![GitHub followers](https://img.shields.io/github/followers/ronangr1.svg?style=social)](https://github.com/ronangr1)
- **Contributors** - *Contributor* - [![GitHub contributors](https://img.shields.io/github/contributors/ronangr1/m2-cmsimportexport.svg?style=flat-square)](https://github.com/ronangr1/m2-cmsimportexport/graphs/contributors)

## License

This project is licensed under the MIT License - see the [LICENSE](./LICENSE) details.

***That's all folks!***
