# apd  

__WARNING: This repo has been moved to https://github.com/tropotek/apd__  

_Date: 19/4/24 8:01 AM_

----

__Project:__ APD (Anatomic Pathology Database)  
__Web:__ <https://github.com/tropotek/apd>  
__Authors:__ Michael Mifsud <http://www.tropotek.com/>  
__Steakholders:__ Andrew Stent <andrew.stent@unimelb.edu.au> Richard Ploeg <richard.ploeg@unimelb.edu.au>


## Contents

- [Installation](#installation)
- [Introduction](#introduction)
- [Upgrade](#upgrade)
- [Documentation](docs/index.md)
- [Changelog](changelog.md)


## Introduction

Anatomic Pathology Database


## Installation

Start by getting the dependant libs:

~~~bash
# git clone git@bitbucket.org:fvas-elearning/apd.git
# cd apd
# composer install
~~~

This should prompt you to answer a few questions and create the `src/config/config.php` and .htaccess files.

If this fails you need to create your own `.htaccess` (copy the `.htaccess.in`) and config.php (copy the `config.php.in`)
Also you will have to run the command `bin/cmd migrate` to install the DB.

## Upgrade

Call the command `bin/cmd upgrade` and if all is well you will get the newest version installed




