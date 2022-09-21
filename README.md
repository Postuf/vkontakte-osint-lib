# Vkontakte OSINT scenario library

[![Gitpod ready-to-code](https://img.shields.io/badge/Gitpod-ready--to--code-blue?logo=gitpod)](https://gitpod.io/#https://github.com/Postuf/vkontakte-osint-lib)

[![Build Status](https://travis-ci.org/postuf/vkontakte-osint-lib.svg?branch=master)](https://travis-ci.org/postuf/vkontakte-osint-lib) [![codecov](https://codecov.io/gh/Postuf/vkontakte-osint-lib/branch/master/graph/badge.svg)](https://codecov.io/gh/Postuf/vkontakte-osint-lib)

## Description

Vkontakte API from official api.

## Rationale

Vkontakte protocol https://vk.com/dev/SDK has technically thorough and detailed documentation, but does not give an opportunity to configure request parameters.
With our libi you can easily set any cURL configuration. Our library makes it possible to get information on profiles. You can get

* status
* timestamp
* photo(avatar)

## Requirements

* PHP 7.4+
* Composer
  * phpseclib

## Docs
* [Create scenario](docs/create-scenario.md)

## QuickStart

First of all, add library to your app user composer:

```
composer require postuf/vkontakte-api-lib
```

To check out usage examples, go to `examples` dir.

# Limitations

There is a limit on the number of profiles for which you can immediately get information. If the figure exceeds 800, you will get an exception
