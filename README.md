# Sodium 2.0a Documentation

## Doxygen

You can build the framework's documentation with <a href="http://doxygen.org">Doxygen</a>.

## Introduction

This project started as a bunch of PHP functions and classes I used for creating
sites. The code eventually made it to a small package, that I used, whenever I
started working on a new project.

As time went on, I deceided to make it better, and rewrite it to a small
framework. Sodium was a word I created as a child, when I was bored in school.
Even though I realized it is already in use, I still thought it is perfect for
my first, great project :)

Sodium reflects my style of writing PHP code, made for my personal use. It is
not a framework that wants to follow any standard rules, habits, design
principles or whatever else. It builds from my experience of coding sites and
services.

The framework aims to be very simple and fast. It is not a huge library, that
will try to define the way you code your project. It is rather a swiss army
knife with great expandability, that tries to help you when you need it. You do
not have to use the whole system, if you want to benefit from its power.

## Installation and update

Sodium is designed to be installed and updated with a single step. If you follow
the rules described in the configuration section, you will not have problems
installing or updating the framework.

To install or update the framework, just extract and/or copy the files to your
server. If you can use Git, you can pull the new version from GitHub directly.

> If you separated your public and non-public files, copy 'index.php' to the
> public directory, and the rest of the package to your safe directory. In this
> case, you have to change the configuration file's path in 'index.php', every
> time you overwrite it.

## Configuration

## Serving pages

This section briefly explains the process of serving a single page, with the
help of the framework. The process can be divided into the following parts:

* Initialization

### Initialization

First of all, the configuration file is included. The configuration
file contains the standard configuration of the framework, and all
your extra initialization needs.

> Only framework related custom initialization should go here. If you
> simply want to run a piece of code every time Sodium gets initialized,
> you should create a module with the sodiumInitialization() method.
