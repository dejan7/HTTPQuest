RESTQuest
===================
##The problem:
PHP by default parses request data only for GET and POST requests and puts them in well-known `$_GET` and `$_POST` variables. Additionally it does so only for `multipart/form-data` and `application/x-www-form-urlencoded` enctypes (Content-type HTTP header).

So if you are trying to build a REST API in plain PHP (without a framework) which utilizes other HTTP verbs like PUT or PATCH - it turns out to be a pain. This package attempts to solve that by doing the boring parsing for you and making the request data available for PUT and PATCH as well.

##How to install?
`composer require dejan7/restquest:0.1.0`

##How to use?
Instantiate the class and call `parse()` method somewhere near the beginning of your app (e.g. during bootstrapping).
```
$RESTquest = new \RESTQuest\RESTQuest();
$RESTquest->parse();
```

If the current request method and content type is suppored, you will have your data available in $_POST.

##Currently supported cases
PHP puts the stuff in `$_GET`/ `$_POST ` by default for following cases:

 * **GET**: Content-types:
	 * any
 * **POST** : Content-types:
	 * `application/x-www-form-urlencoded`
	 * 	`multipart/form-data`

RESTQuest enhances this and puts stuff in `$_POST` for additional cases:

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

##FAQ

 1. **Why it parses the request into $_POST always, even for PATCH and PUT?**

 Even though it feels slightly dirty, i feel like it's a better choice than creating new global variables, because you can use a package like [patricklouys/http](https://github.com/PatrickLouys/http) or  [sabre/http](https://github.com/fruux/sabre-http) etc. on top of this one and get other cool features for request/response manipulation without any modifications.

 2. **What about files?**

 PHP by default uploads and puts files easily accessible in `$_FILES` variable only for POST request, `multipart/form-data` enctype. Currently RESTQuest doesn't add functionality to process files for PUT and PATCH requests, though i'd like to add that in the future. Contributions welcome! You have these options for file uploads:
 a) Use POST requests whenever you are doing file uploads (PHP populates $_FILES automatically)
 b) Create a PUT/PATCH endpoint that accepts only files. Read the raw contents of the request with `file_get_contents('php://input');` and save it.
 c) Use an established framework like Laravel/Symfony

##Disclaimer
This package is in early stages and in active development, it's not ready for production yet.


##License
This is an open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
