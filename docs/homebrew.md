# PHP RESTful API using Apache's Fallback and PHP Autoload

The two key motivators for this approach as opposed to deploying a Laravel build are three-fold:

1. Speed to deployment - Building a light-weight custom implementation might be faster than learning Laravel from scratch and then customizing to fit the application
2. Laravel is a rapidly moving target, with weekly patches and minor revisions every 4-5 months
3. 

The secret sauce is combining Apache's `FallbackResource` pragma with PHP's Autoload.

The JSON API Specification can be found here: https://tsh.io/blog/json-api-how-to-create-api-in-php/

Useful RESTful API design notes can be read here: https://www.moesif.com/blog/technical/api-design/REST-API-Design-Filtering-Sorting-and-Pagination/

## Apache FallbackResource (FBR)

Apache's `FallbackResource` (FBR) invokes a static endpoint when a non-existing endpoint within a given directory is requested. The FBR directory can be configured from Apache's configuration file, or using a hidden `.htaccess` file in the desired directory.

To implement RESTful API under the `/api` directory, we add the `.htaccess` file redirecting any requested endpoints to a single master endpoint at `/api/index.php`. The master endpoint parses the HTTP request and performs a route lookup.

## PHP Autoloading

PHP provides a method to dynamically load classes, `spl_autoload_register()` (https://www.php.net/manual/en/function.spl-autoload-register.php). We can then implement our RESTful API as a collection of classes, with each endpoint corresponding to a PHP class.

## Secure Sessions

### Dynamic Roles and Permissions

## Endpoints

The table below illustrates some use cases, using the idea of a Pet Adoption Agency as an example. For the Pet Adoption Agency, let us consider the case where animals are registered to a central database, and removed from that database when the animal is adopted.

| CRUD | HTTP Method | Endpoint | Example |
| --- | --- | --- | --- |
| Read All | GET | `/api/<class>` | `/api/cat` |
| Read | GET | `/api/<class>/<uuid>` | `/api/cat/1234` |
| Write | POST | `/api/<class>` | `/api/dog` |
| Delete | DELETE | `/api/<class>/<uuid>` | `/api/dog/5678` |

The read method shall read the database for a given class. If the class is not available, the HTTP response shall be *400 Bad Request*. If the uuid is not found, then the HTTP response shall be *404 Not Found*.

The write method shall require that the data to be written is an object with a `uuid` property, or an array of objects, each with a `uuid` property. 

## Conditional Reads and Deletes

Conditional reads (queries) and deletes can be performed using the `filter` keyword.

The filter uses the following endpoint format:

	/api/<class>?filter

Inequalities are denoted using the following keywords: `eq:`, `ne:`, `lt:`, `gt:`, `lte:`, `gte:`. Conjunctions are denoted using the `and:` and `or:` keywords. Filter strings must be URL-escaped.
	
Examples:

	HTTP GET    /api/cat?filter[breed]=tabby
	HTTP DELETE /api/dog?filter[age.weeks]=lt:10
	HTTP GET    /api/cat?filter[breed]=tabby&or:filter[breed]=dsh
	
The more powerful filter uses the following endpoint format:

	/api/<class>?q=encodedQuery


## Pagination

The keyphrases `page.start`, `page.size`, `sort.order` shall be reserved and can be used for pagination in the query.

Examples:

	HTTP GET /api/cat?page.start=30&page.size=10&sort.order=age.years:breed:name
	HTTP GET /api/cat?breed=eq:tabby&page.start=0&page.size=10
	HTTP GET /api/cat?q=YnJlZWQ9ZHNo&page.start=50&page.size=25

# Semi-Structured Data Model

## YAML Data Model Specification

## 

# PHP WebSockets using Ratchet

http://socketo.me/
