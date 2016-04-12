# Jobvite

[![Build Status](https://travis-ci.org/jeremylivingston/jobvite.svg?branch=master)](https://travis-ci.org/jeremylivingston/jobvite)

PHP library for interacting with Jobvite's job feed API

## Installation

The suggested installation method is via [composer](https://getcomposer.org/):

```sh
php composer.phar require jeremylivingston/jobvite
```

After installing the Jobvite library, simply create a new instance of the client and call the `getJobFeed` method:

```php
<?php

use Livingstn\Jobvite\Client;

$client = new Client('companyId', 'apiKey', 'secretKey');
$jobFeed = $client->getJobFeed();

```

A PHP stdClass will be returned containing all of the fields in the API response. View the Jobvite documentation for
a full example response: http://careers.jobvite.com/careersites/JobviteWebServices.pdf

Optional filters can be provided to the `getJobFeed` method as follows:

```php
<?php

use Livingstn\Jobvite\Client;

$client = new Client('companyId', 'apiKey', 'secretKey');
$jobFeed = $client->getJobFeed(['type' => 'Full-time', location' => 'Detroit, MI, USA']);

```

At the time of writing, any of the following filters can be used:

* **type** - Job type, configured in Admin
* **availableTo** - Publishing settings for your jobs
* **category** - The categories used on your career site, configured in Admin
* **location** - City, state, Country
* **region** - Region, configured in Admin
* **start** - Denotes the starting index. (Default: 1)
* **count** - Denotes the number of jobs from the starting index (Default: 100)