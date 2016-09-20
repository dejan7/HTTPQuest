[![Build Status](https://travis-ci.org/dejan7/HTTPQuest.svg?branch=master)](https://travis-ci.org/dejan7/HTTPQuest)

HTTPQuest
===================
HTTPQuest is a lightweight PHP polyfill that can parse incoming request body for any HTTP verb.

##The problem:
PHP by default parses request data only for GET and POST requests and puts them in well-known `$_GET` and `$_POST` and `$_FILES` variables. Additionally it does so only for `multipart/form-data` and `application/x-www-form-urlencoded` enctypes (Content-type HTTP header).

So if you are trying to build a REST API in plain PHP (without a framework) which utilizes other HTTP verbs like PUT or PATCH - it turns out to be a pain. This package attempts to solve that by doing the boring parsing for you and making the request data available for any type of request as well.

##How to install?
`composer require dejan7/httpquest:0.3.0`

##How to use?
Instantiate the class and call the `decode` method somewhere near the beginning of your app (e.g. during bootstrapping).
```
$HTTPQuest = new \HTTPQuest\HTTPQuest();
$HTTPQuest->decode($_POST, $_FILES);
```
And that's all, your parsed data will now be in variables that you passed to the `decode` method (`$_POST`, and `$_FILES` in this case).

PHP puts stuff in `$_POST` and `$_FILES` by default for following cases:

 * **POST** : Content-types:
	 * `application/x-www-form-urlencoded`
	 * 	`multipart/form-data`


HTTPQuest enhances this and you can configure it to parse data for any method/content-type combination.
The default options are set to this:

* **POST**: Content-types:
	* `application/json`
* **PUT**: Content-types:
	 * `application/x-www-form-urlencoded`
	 * 	`multipart/form-data`
	 * `application/json`
* **PATCH**: Content-types:
	 * `application/x-www-form-urlencoded`
	 * 	`multipart/form-data`
	 * `application/json`

However, you can change the defaults by passing a `HTTPQuestOptions` instance to the constructor like this:
```
include __DIR__ . "/../vendor/autoload.php";

use HTTPQuest\HTTPQuestOptions;
use HTTPQuest\Requests;
use HTTPQuest\ContentTypes;


$opts = new HTTPQuestOptions();

$opts->forMethod(Requests::GET)
    ->parse(ContentTypes::X_WWW_FORM_URLENCODED);

$opts->forMethod(Requests::PATCH)
    ->parse(ContentTypes::FORMDATA)
    ->parse(ContentTypes::JSON);

$HTTPQuest = new \HTTPQuest\HTTPQuest(
    $_SERVER,
    "php://input",
    $opts
);
```

**Wait! Are you telling me that it can parse request body even for GET requests?**
That's correct. Now whether you will utilize such scenarios - i leave the choice to you. You can read some of the discussions on StackOverflow and decide for yourself
[http://stackoverflow.com/questions/978061/http-get-with-request-body](http://stackoverflow.com/questions/978061/http-get-with-request-body)
[http://stackoverflow.com/questions/299628/is-an-entity-body-allowed-for-an-http-delete-request](http://stackoverflow.com/questions/299628/is-an-entity-body-allowed-for-an-http-delete-request)

##Files
HTTPQuest also parses the files from incoming requests and tries it's best to mimic PHP's default behavior with `$_FILES`. However, the only caveat is that you can't use `move_uploaded_file` PHP function on requests other than POST. On other requests `move_uploaded_file` thinks that the file wasn't uploaded with PHP and it doesn't execute. The workaround is to use `copy` like this:

```
$HTTPQuest = new \HTTPQuest\HTTPQuest();
$HTTPQuest->decode($_POST, $_FILES);

copy($_FILES["myfile"]["tmp_name"], "/some/dir" . $_FILES["myfile"]["name"]);
```

##Credits
[Russel](https://github.com/sndsgd) for [sndsgd/http](https://github.com/sndsgd/http), HTTPQuest uses his multipart/form-data decoding logic.

##License
This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
