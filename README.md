# DepMon (Dependency Monitoring)

## What does it do?

DepMon helps you getting a better overview on your projects and their [composer](http://composer.org/) dependencies. It is inspired by monitoring applications like [david-dm](https://david-dm.org/), to get notified if your project dependencies are not longer up to date. Instead of the public web variants, DepMon is a [symfony](http://symfony.com/) application, which you can host on your own.

## Project requirements

The Dependency Monitoring does'nt need access to your production environments. Instead, the system fetches the data from the VCS. This is only usefull, if your _master_ branch represents a production-ready state, which is identical to the real project system. 

So from that, it is necessary that the system, where DepMon is running on, needs access to the different project repositories. 

## How does it work?

_ToDo_

## Installation

_ToDo_

## Configuration

To add a new project to DepMon, you need to extend the ``depmon.projects.yml`` file like the example is showing below.

```
projects:
    -
        name: 'project-name'
        git: 'git-url'
        path: 'path-to-composer-files'
```

The data aggregation starts with following command. It is recommended to use a cronjob for regulary updates. 

```
php bin/console app:aggregate
```

## ToDo 

_ToDo_