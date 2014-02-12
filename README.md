[![Build Status](https://travis-ci.org/Magomogo/translator-utils.png)](https://travis-ci.org/Magomogo/translator-utils)

# About

Utils for localization with Translator application: parsing out the i18n keys from `phtml`
templates, AngularJS partials, and `json` data; with the subsequent storage in CouchDB.

# Requirements

* php5-intl

# Dev requirements

* Nodejs package manager (npm) in PATH
* couchdb

CouchDb schema is developed separately as npm package [translator-couch](https://www.npmjs.org/package/translator-couch)

# Testing

To execute Unit tests run:

    ./tests/run.sh --testsuite Unit

Integration tests are depends on the database server `couchdb` and the package manager `npm`.
They can be started this way:

    ./tests/run.sh --testsuite Integration

# License: MIT

[Copyright (c) 2013 Maxim Gnatenko](http://opensource.org/licenses/MIT)
