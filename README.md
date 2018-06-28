DepMon (Dependency Monitoring)
=========

[![Build Status](https://travis-ci.org/xima-media/depmon.svg?branch=master)](https://travis-ci.org/xima-media/depmon)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/xima-media/depmon/badges/quality-score.png)](https://scrutinizer-ci.com/g/xima-media/depmon/)
[![Packagist](https://img.shields.io/packagist/v/xima/depmon-bundle.svg)](https://packagist.org/packages/xima/depmon-bundle)

<img src="https://raw.githubusercontent.com/xima-media/depmon/master/doc/images/logo.png" alt="DepMon Logo" align="center" />

## What does it do?

DepMon helps you getting a better overview on your projects and their [composer](http://composer.org/) dependencies. It is inspired by monitoring applications like [david-dm](https://david-dm.org/), to get notified if your project dependencies are not longer up to date. Instead of the public web variants, DepMon is a [symfony](http://symfony.com/) application, which you can host on your own.

## Project requirements

The Dependency Monitoring does'nt need access to your production environments. Instead, the system fetches the data from the VCS. This is only usefull, if your _master_ branch represents a production-ready state, which is identical to the real project system. 

So from that, it is necessary that the system, where DepMon is running on, needs access to the different project repositories. 

## How does it work?

_ToDo_

## Installation

Follow the installation steps in the [wiki](https://github.com/xima-media/depmon/wiki/Installing-DepMon).

## Configuration

To add a new project to DepMon, you need to extend the _parameters_ section in the ``service.yaml`` file like the example is showing below.

```
xima_depmon.projects:
    -
        name: 'project-name'
        git: 'git-url'
        path: 'path-to-composer-files'
```

The data aggregation starts with following command. It is recommended to use a cronjob for regulary updates. 

```
php bin/console depmon:aggregate
```