# api-generator
PHP-code generator (based on OAS) for Laravel framework, with complete support of JSON-API data format  [![Tweet](http://jpillora.com/github-twitter-button/img/tweet.png)](https://twitter.com/intent/tweet?text=Generate%20api%20code%20for%20Laravel%20and%20json-api%20based%20on%20OAS%20&url=https://github.com/RJAPI/api-generator&hashtags=php,api,oas,raml,json-api,laravel,developers)

[![Build Status](https://scrutinizer-ci.com/g/SoliDry/api-generator/badges/build.png?b=master)](https://scrutinizer-ci.com/g/SoliDry/api-generator/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SoliDry/api-generator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SoliDry/api-generator/?branch=master)
[![Total Downloads](https://poser.pugx.org/solidry/api-generator/downloads)](https://packagist.org/packages/solidry/api-generator)
[![Latest Stable Version](https://poser.pugx.org/solidry/api-generator/v/stable)](https://packagist.org/packages/solidry/api-generator)
[![Code Intelligence Status](https://scrutinizer-ci.com/g/SoliDry/api-generator/badges/code-intelligence.svg?b=master)](https://scrutinizer-ci.com/code-intelligence)
[![codecov](https://codecov.io/gh/RJAPI/api-generator/branch/master/graph/badge.svg)](https://codecov.io/gh/RJAPI/api-generator)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

![alt OAS logo](https://github.com/RJAPI/api-generator/blob/develop/tests/images/OpenAPI_Logo_Pantone-1.png)
![alt Laravel logo](https://github.com/RJAPI/api-generator/blob/master/tests/images/laravel-logo-white.png)
![alt JSON API logo](https://github.com/RJAPI/api-generator/blob/master/tests/images/jsonapi.png)

JSON API support turned on by default - see `Turn off JSON API support` section bellow 

* [Installation](#user-content-installation-via-composer)
    * [Configuration](#user-content-laravel-specific-configuration)
    * [Running generator](#user-content-running-generator)
    * [Docker repo](#user-content-docker-repository)
* [Open API Types and Declarations](#user-content-open-api-types-and-declarations)    
* [Generated files content](#user-content-generated-files-content)
    * [Module Config](#user-content-module-config)
    * [Controllers](#user-content-controllers)
    * [FormRequests](#user-content-formrequests)
    * [Models](#user-content-models)
    * [Routes](#user-content-routes)
    * [Migrations](#user-content-migrations)
    * [Tests](#user-content-tests)
* [Relationships](#user-content-relationships-particular-qualities)
* [Bulk Extension](#user-content-bulk-extension)
* [Query parameters](#user-content-query-parameters)
* [Security](#user-content-security)
    * [Static access token](#user-content-static-access-token)
    * [JWT](#user-content-jwt-json-web-token)
* [Caching](#user-content-caching)
* [Soft Delete](#user-content-soft-delete)
* [Tree structures](#user-content-tree-structures)
* [Finite-state machine](#user-content-finite-state-machine)
* [Spell check](#user-content-spell-check)
* [Bit mask](#user-content-bit-mask)
* [Custom SQL](#user-content-custom-sql)
* [Custom business logic](#user-content-custom-business-logic)
* [Regeneration](#user-content-regeneration)

### Installation via composer:
First of all - create Laravel project if you didn't do that yet:
```
composer create-project --prefer-dist laravel/laravel your_app
```

then in your project directory run:
``` 
composer require SoliDry/api-generator
```

### Laravel specific configuration

Add command to ```$commands``` array in ```app/Console/Kernel.php```
```php
protected $commands = [
    ApiGenerator::class,
];
```

#### Add Service Provider

Next add the following service provider in `config/app.php`.

```php
'providers' => [
  Nwidart\Modules\LaravelModulesServiceProvider::class,
],
```

Next, add the following aliases to `aliases` array in the same file.

```php
'aliases' => [
  'Module' => Nwidart\Modules\Facades\Module::class,
],
```

Next publish the package's configuration file by running :

```
php artisan vendor:publish --provider="Nwidart\Modules\LaravelModulesServiceProvider"
```

#### Autoloading

By default Controllers, entities or repositories are not loaded automatically. You can autoload your modules using `psr-4`. For example :

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/"
    }
  }
}
```

refresh changes by running: 
```
composer dump-autoload
```

### Running generator

Run in console:
```
php artisan api:generate oas/openapi.yaml --migrations
```

This command creates the whole environment for you to proceed building complex API based on OAS/Laravel/JSON API, 
in particular: directories for modular app, Controllers/FormRequests/Models+Pivots to support full MVC, 
Routes (JSON API compatible) and even migrations to help you create RDBMS structure.
 
```oas/openapi.yaml``` - file in oas directory in the root of your project, 
which should be prepared before or you may wish to just try by copying an example from ``` tests/functional/oas/openapi.yaml```

Options:

```--migrations``` is an option to create migrations (create_entityName_table) for every entity + pivots if there are ManyToMany relationships.

```--regenerate``` use this if you need to rewrite all files generated previously. 
By default generated files preserved to prevent overwriting of added/modified content.   

The output will look something like this:
![Console output](https://github.com/RJAPI/api-generator/blob/master/tests/images/Console_generator_output.png)

After that u can see the following dirs and files module structure in your project:
![Dirs and files](https://github.com/RJAPI/api-generator/blob/master/tests/images/Dirs_and_files_module_structure.png)

### Docker repository
Another way of installing and playing with api-generator (in sandbox fashion) is via public docker repo:
```bash
docker pull zeusakm/ubuntuforapi
```

There will be pre-installed Laravel 5.5.* application in `/var/www/api-generator` + php7.1/php7.1-fpm/nginx/mysql (with migrated tables) etc.

We'll be glad to any contribution for docker repository.

### Open API Types and Declarations
OAS (Open API Specification) was developed as merge of Swagger and RAML specs by two groups of developers (they tired to argue with each other :smile:), thus it became quite popular and 
has been implemented for api-generator

```YAML
openapi: 3.0.1
info: This api provides access to articles
servers:
- url: https://{environment}.example.com:{port}/{basePath}
  description: Production server
  variables:
    environment:
      default: api
      description: An api for devices at Google dot com
    port:
      enum:
        - 80
        - 443
      defualt: 80
    basePath:
      default: v3 # this version will be used as Modules subdirectory and base path uri in routes e.g. /Modules/V2/ and /v2/articles 
# to declare globally which files to include with other components declarations
uses:
  topics: oas/topic.yaml
```
U can set multiple servers as well as multiple files into the main `openapi.yaml`, thus code will be generated for every server module e.g.: Modules/v2, Modules/v3, Modules/v4 
and there will be other Types from different files. 

Basic and custom Types are declared under
```yaml
components:
  schemas:
``` 

Types ``` ID, Type, DataObject/DataArray``` are special helper Types - !required
 
You can easily add `string` IDs to entities you'd like for example `SID` can be placed in `Article` entity like that `id: SID` - api-generator 
will produce migrations, relations and models respectively. 
```yaml
  ID:
    type: integer
    required: true
    # it will be BIGINT UNSIGNED in migration Schema if maximum > 10
    maximum: 20
  SID:
    type: string
    required: true
    maxLength: 128    
  Type:
    type: string
    required: true
    minLength: 1
    maxLength: 255
  DataObject:
    type: object
    required: true
  DataArray:
    type: array
    required: true
```

Special data type ``` RelationshipsDataItem ``` - !required
```yaml
  RelationshipsDataItem:
    type: object
    properties:
      id: ID
      type: Type
```
defined in every relationship custom type

Attributes ```*Attributes``` are defined for every custom Object ex.:
```yaml
  ArticleAttributes:
    description: Article attributes description
    type: object
    properties:
      title:
        required: true
        type: string
        minLength: 16
        maxLength: 256
        facets:
          index:
            idx_title: index
      description:
        required: true
        type: string
        minLength: 32
        maxLength: 1024
      url:
        required: false
        type: string
        minLength: 16
        maxLength: 255
        facets:
          index:
            idx_url: unique        
      show_in_top:
        description: Show at the top of main page
        required: false
        type: boolean
      status:
        description: The state of an article
        enum: ["draft", "published", "postponed", "archived"]
      topic_id:
        description: ManyToOne Topic relationship
        required: true
        type: integer
        minimum: 1
        maximum: 6
        facets:
          index:
            idx_fk_topic_id: foreign
            references: id
            on: topic
            onDelete: cascade
            onUpdate: cascade        
      rate:
        type: number
        minimum: 3
        maximum: 9
        format: double     
```

Relationships custom type definition semantics ```*Relationships```
```yaml
  TagRelationships:
    description: Tag relationship description
    type: object
    properties:
      data:
        type: DataArray
        items:
          type: RelationshipsDataItem
```

Complete composite Object looks like this: 
```yaml
  Article:
    type: object
    properties:
      type: Type
      id: ID
      attributes: ArticleAttributes
      relationships:
        type: TagRelationships[] | TopicRelationships     
```
That is all that api-generator needs to provide code structure that just works out-fo-the-box within Laravel framework, 
where may any business logic be applied.

To use multiple files processing add (as root element):
```yaml
uses:
  topics: oas/openapi.yaml
  otherfile: oas/otherFile.yaml
  yetanother: oas/yetanother.yaml
```
all files will be generated as if they were one composite object.


To set default values for GET query parameters - set QueryParams like this:
```yaml
  QueryParams:
    type: object
    properties:
      page:
        type: integer
        required: false
        default: 10
        description: page number
      limit:
        type: integer
        required: false
        default: 15
        description: elements per page
      sort:
        type: string
        required: false
        pattern: "asc|desc"
        default: "desc"
      access_token:
        type: string
        required: true
        example: db7329d5a3f381875ea6ce7e28fe1ea536d0acaf
        description: sha1 example
        default: db7329d5a3f381875ea6ce7e28fe1ea536d0acaf        
```
it will be used on requests similar to: ```http://example.com/v1/article?include=tag``` 
where no params were passed.  

Complete directory structure after generator will end up it`s work will be like:
```php
Modules/{ModuleName}/Http/Controllers/ - contains Controllers that extends the DefaultController (descendant of Laravel's Controller)
Modules/{ModuleName}/Http/FormRequest/ - contains forms that extends the BaseFormRequest (descendant of Laravel's FormRequest) and validates input attributes (that were previously defined as *Attributes)
Modules/{ModuleName}/Entities/ - contains mappers that extends the BaseModel (descendant of Laravel's Model) and maps attributes to RDBMS
Modules/{ModuleName}/Http/routes.php - contains routings pointing to Controllers with JSON API protocol support
Modules/{ModuleName}/Database/Migrations/ - contains migrations created with option --migrations
```

### Generated files content

#### Module Config
```php
<?php
return [
    'name' => 'V1',
    'query_params'=> [
        'limit' => 15,
        'sort' => 'desc',
        'access_token' => 'db7329d5a3f381875ea6ce7e28fe1ea536d0acaf',
    ],
    'trees'=> [
        'menu' => true,
    ],
    'jwt'=> [
        'enabled' => true,
        'table' => 'user',
        'activate' => 30,
        'expires' => 3600,
    ],
    'state_machine'=> [
        'article'=> [
            'status'=> [
                'enabled' => true,
                'states'=> [
                    'initial' => ['draft'],
                    'draft' => ['published'],
                    'published' => ['archived', 'postponed'],
                    'postponed' => ['published', 'archived'],
                    'archived' => [''],
                ],
            ],
        ],
    ],
    'spell_check'=> [
        'article'=> [
            'description'=> [
                'enabled' => true,
                'language' => 'en',
            ],
        ],
    ],
    'bit_mask'=> [
        'user'=> [
            'permissions'=> [
                'enabled' => true,
                'hide_mask' => true,
                'flags'=> [
                'publisher' => 1,
                'editor' => 2,
                'manager' => 4,
                'photo_reporter' => 8,
                'admin' => 16,
                ],
            ],
        ],
    ],
    'cache'=> [
        'tag'=> [
            'enabled' => true,
            'stampede_xfetch' => false,
            'stampede_beta' => 1.1,
            'ttl' => 3600,
        ],
        'article'=> [
            'enabled' => true,
            'stampede_xfetch' => true,
            'stampede_beta' => 1.5,
            'ttl' => 300,
        ],
    ],    
];
```

#### Controllers
Entity controller example:
```php
<?php
namespace Modules\V1\Http\Controllers;

class ArticleController extends DefaultController 
{
}
```
By default every controller works with any of GET - index/view, POST - create, PATCH - update, DELETE - delete methods.
Thus you don't need to implement anything special here.

DefaultController example:
```php
<?php
namespace Modules\V1\Http\Controllers;

use SoliDry\Extension\BaseController;

class DefaultController extends BaseController 
{
}
```
To provide developer-based (user-space) implementation of certain logic for all Controllers.

#### FormRequests
Validation BaseFormRequest example:
```php
<?php
namespace Modules\V2\Http\Requests;

use SoliDry\Extension\BaseFormRequest;

class ArticleFormRequest extends BaseFormRequest 
{
    // >>>props>>>
    public $id = null;
    // Attributes
    public $title = null;
    public $description = null;
    public $url = null;
    public $show_in_top = null;
    public $status = null;
    public $topic_id = null;
    public $rate = null;
    public $date_posted = null;
    public $time_to_live = null;
    public $deleted_at = null;
    // <<<props<<<

    // >>>methods>>>
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array 
    {
        return [
            'title' => 'required|string|min:16|max:256|',
            'description' => 'required|string|min:32|max:1024|',
            'url' => 'string|min:16|max:255|',
                // Show at the top of main page
            'show_in_top' => 'boolean',
                // The state of an article
            'status' => 'in:draft,published,postponed,archived|',
                // ManyToOne Topic relationship
            'topic_id' => 'required|integer|min:1|max:6|',
            'rate' => '|min:3|max:9|',
            'date_posted' => '',
            'time_to_live' => '',
            'deleted_at' => '',
        ];
    }

    public function relations(): array 
    {
        return [
            'tag',
            'topic',
        ];
    }
    // <<<methods<<<
}
```
#### Models

BaseModel example:
```php
<?php
namespace Modules\V2\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use SoliDry\Extension\BaseModel;

class Article extends BaseModel 
{
    use SoftDeletes;

    // >>>props>>>
    protected $dates = ['deleted_at'];
    protected $primaryKey = 'id';
    protected $table = 'article';
    public $timestamps = false;
    public $incrementing = false;
    // <<<props<<<
    // >>>methods>>>

    public function tag() 
    {
        return $this->belongsToMany(Tag::class, 'tag_article');
    }
    public function topic() 
    {
        return $this->belongsTo(Topic::class);
    }
    // <<<methods<<<
}
```

#### Routes

Routes will be created in ```/Modules/{ModuleName}/Http/routes.php``` file, for every entity defined in yaml:
```php
// >>>routes>>>
// Article routes
Route::group(['prefix' => 'v2', 'namespace' => 'Modules\\V2\\Http\\Controllers'], function()
{
    // bulk routes
    Route::post('/article/bulk', 'ArticleController@createBulk');
    Route::patch('/article/bulk', 'ArticleController@updateBulk');
    Route::delete('/article/bulk', 'ArticleController@deleteBulk');
    // basic routes
    Route::get('/article', 'ArticleController@index');
    Route::get('/article/{id}', 'ArticleController@view');
    Route::post('/article', 'ArticleController@create');
    Route::patch('/article/{id}', 'ArticleController@update');
    Route::delete('/article/{id}', 'ArticleController@delete');
    // relation routes
    Route::get('/article/relationships/{relations}', 'ArticleController@relations');
    Route::post('/article/relationships/{relations}', 'ArticleController@createRelations');
    Route::patch('/article/relationships/{relations}', 'ArticleController@updateRelations');
    Route::delete('/article/relationships/{relations}', 'ArticleController@deleteRelations');
});
// <<<routes<<<
```
As you may noticed there are relationships api-calls and bulk Extension batch queries support.  

#### Migrations

Generated migrations will look like standard migrations in Laravel:
```php
<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration 
{
    public function up() 
    {
        Schema::create('article', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 256);
            $table->index('title', 'idx_title');
            $table->string('description', 1024);
            $table->string('url', 255);
            $table->unique('url', 'idx_url');
            // Show at the top of main page
            $table->unsignedTinyInteger('show_in_top');
            $table->enum('status', ["draft","published","postponed","archived"]);
            // ManyToOne Topic relationship
            $table->unsignedInteger('topic_id');
            $table->foreign('topic_id', 'idx_fk_topic_id')->references('id')->on('topic')->onDelete('cascade')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    public function down() 
    {
        Schema::dropIfExists('article');
    }

}
```
Note, that U have an ability to make any ranges for varchar, integer types through minLength/maxLength and minimum/maximum respectively. 
For instance, integer can be set to unsigned smallint with 
`minimum: 1` (any number > 0) and `maximum: 2` (any number <= 3 to fit smallint db type range).  

All migrations for specific module will be placed in ``` Modules/{ModuleName}/Database/Migrations/ ```

To execute them all - run: ``` php artisan module:migrate ```

Also worth to mention - Laravel uses table_id convention to link tables via foreign key.
So U can either follow the default - add to yaml an id that matches to the table name 
(just like in example: `topic_id` -> in article table for topic table `id`, see `ArticleAttributes` in OAS Types and Declarations) 
or make your own foreign key and add it to ```hasMany/belongsTo -> $foreignKey``` parameter in generated BaseModel entity.

Additionally, to specify index for particular column you can add a `facets` property like this:
```yaml
    # regular index
    facets:
      index:
        idx_title: index            
        
    # unique key    
    facets:
      index:
        idx_url: unique             
        
    # foreign key
    facets:
      index:
        idx_fk_topic_id: foreign
        references: id
        on: topic
        onDelete: cascade
        onUpdate: cascade
```
to existing columns.

However, there are situations where you have to create composite indices:
```yaml
      last_name:
        required: false
        type: string
        minLength: 16
        maxLength: 256
        facets:
          composite_index:
            index: ['first_name', 'last_name'] # can be unique, primary
```
an example for foreign key would be like: 
```yaml
    facets:
      composite_index:
        foreign: ['first_column', 'second_column'] 
        references: ['first_column', 'second_column']
        on: first_second
        onDelete: cascade
        onUpdate: cascade        
``` 

#### Tests
To provide convenient way for integration/functional testing, one can generate tests by providing `--tests` command option, e.g.:
```bash
php artisan api:generate oas/openapi.yaml --migrations --tests
``` 
in command output you'll see the following files have been created:
```bash
tests/functional/ArticleCest.php created
...
tests/functional/TagCest.php created
``` 
For more info on how to set an environment for functional tests in Laravel - see https://codeception.com/for/laravel 

### Relationships particular qualities
To let generator know about what a particular relationship to apply (ex.: ManyToMany, OneToMany, OneToOne) 
set the ```relationships``` property in an Entity like so - for ex. let's see how to set ManyToOne relationship between Article and Tag entities.

Define Article with relationships like:
```
relationships:
  type: TagRelationships[]
```
and Tag with relationships like:
```
relationships:
  type: ArticleRelationships
```
This way you telling to generator: "make the relation between Article and Tag OneToMany from Article to Tag"
The idea works with any relationship you need - ex. ManyToMany: ```TagRelationships[] -> ArticleRelationships[]```, 
OneToOne: ```TagRelationships -> ArticleRelationships```

You can also bind several relationships to one entity, for instance - 
you have an Article entity that must be bound to TagRelationships and TopicRelationships, this can be done similar to:
```
relationships:
    type: TagRelationships[] | TopicRelationships
```
or vise versa 
```
relationships:
    type: TopicRelationships | TagRelationships[]
```
Generator will independently detect all relationships between entities.

### Query parameters

You may want to use additional query parameters to fetch includes 
and/or pagination, for instance:
```php
http://example.com/v1/article?include=tag&page=2&limit=10&sort=asc
```

You may not wish to drag all the attributes/fields: 
```php
http://example.com/v1/article/1?include=tag&data=["title", "description"]
```
Note: data array items MUST be set in double quotes.

or you may want to ORDER BY several columns in different directions:
```php
http://example.com/v1/article/1?include=tag&order_by={"title":"asc", "created_at":"desc"}
```

Also, you have an ability to filter results this way:
```php
http://example.com/v1/article?include=tag&filter=[["updated_at", ">", "2017-01-03 12:13:13"], ["updated_at", "<", "2017-01-03 12:13:15"]]
```
those arrays will be put to Laravel where clause and accordingly protected by param bindings. 

The dynamic module name similar to: v1, v2 - will be taken on runtime 
as the last element of the array in ```config/module.php``` file, 
if you, by strange circumstances, want to use one of the previous modules, 
just set one of previously registered modules as the last element of an array.  

An example of auto-generated ```config/module.php```:
```php
<?php
return [
    'modules'=> [
        'v1',
    ]
];
```

To get configuration parameters at runtime generator will create content 
in ```Modules/{ModuleName}/Config/config.php``` file:
```php
<?php
return [
    'name'=>'V1',
    'query_params'=> [
        // default settings
        'limit' => 15,
        'sort' => 'desc',
        // access token to check via global FormRequest
        'access_token' => 'db7329d5a3f381875ea6ce7e28fe1ea536d0acaf',
    ],
];
```

### Bulk Extension

Multiple resources can be created by sending a POST request to a URL that represents a collection of resources.

```http request
POST /photos
Content-Type: application/vnd.api+json; ext=bulk
Accept: application/vnd.api+json; ext=bulk

{
  "data": [{
    "type": "photos",
    "title": "Ember Hamster",
    "src": "http://example.com/images/productivity.png"
  }, {
    "type": "photos",
    "title": "Mustaches on a Stick",
    "src": "http://example.com/images/mustaches.png"
  }]
}
```

Multiple resources can be updated by sending a PATCH request to a URL that represents a collection of resources to which they all belong.

```http request
PATCH /articles
Content-Type: application/vnd.api+json; ext=bulk
Accept: application/vnd.api+json; ext=bulk

{
  "data": [{
    "type": "articles",
    "id": "1",
    "title": "To TDD or Not"
  }, {
    "type": "articles",
    "id": "2",
    "title": "To cache or not"
  }]
}

```

Multiple resources can be deleted by sending a DELETE request to a URL that represents a collection of resources to which they all belong.

```http request
DELETE /articles
Content-Type: application/vnd.api+json; ext=bulk
Accept: application/vnd.api+json; ext=bulk

{
  "data": [
    { "type": "articles", "id": "1" },
    { "type": "articles", "id": "2" }
  ]
}
```

A request completely succeed or fail (in a single "transaction").

Therefore, any request that involves multiple operations only succeed if all operations are performed successfully. 
The state of the server will not be changed by a request if any individual operation fails.

### Security

#### Static access token
In ```QueryParams``` you can declare the ```access_token``` property, that will be placed to ```Modules/{ModuleName}/Config/config.php```.
Generator will create ```Modules\{ModuleName}\Http\Requests\ApiAccessToken.php``` FormRequest.
 
To activate this check on every request - add ApiAccessToken FormRequest to ```app/Http/Kernel.php```, ex.:
```php
class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Modules\V2\Http\Requests\ApiAccessToken::class,
    ];
```
Generated configuration part:
```php
    'query_params'=> [
        'limit' => 15,
        'sort' => 'desc',
        'access_token' => 'db7329d5a3f381875ea6ce7e28fe1ea536d0acaf',
    ],
```

#### JWT (Json Web Token)

To support a JWT check, you need to add to any users, employees, 
customers like table the ```jwt``` and ```password``` properties:
```yaml
  password:
    description: user password to refresh JWT (encrypted with password_hash)
    required: true
    type: string
    maxLength: 255
  jwt:
    description: Special field to run JWT Auth via requests
    required: true
    type: string
    minLength: 256
    maxLength: 512
    default: ' '
```
The ```maxLength``` parameter is important, because of varchar-type sql field will be created with length 512.

The ```default``` value should be equal precisely ' ' - empty string with space.  

JWT specific configuration will be appended by generator in ```Modules/{ModuleName}/Config/config.php```:
```php
    'jwt'=> [
        'enabled' => true,
        'table' => 'user',
        'activate' => 30,
        'expires' => 3600,
    ],
```
U can change those `activate` and `expires` time settings as needed.

To protect key verification in JWT token - place `JWT_SECRET` variable to .env configuration file with secret key value assigned
(secret can be any string at any length, but be wise to use strong one, ex.: hashed with sha1/sha2 etc).

Then put the value to global configuration file `config/app.php`, we need this to apply best practices for caching configs environment. 
```php
'jwt_secret'     => env('JWT_SECRET', 'secret'),
```

As for any standard Laravel middleware register it in ```app/Http/Kernel.php``` :
```php
    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'jwt' => \SoliDry\Extension\BaseJwt::class,    
```

And just use this middleware in any requests U need defining 
it in ```Modules/{ModuleName}/Http/routes.php```, ex: 

To declare JWT check only for one specific route: 
```php
Route::get('/article', 'ArticleController@index')->middleware('jwt');
```

To declare JWT check for routes group: 
```php
Route::group(['middleware' => 'jwt', 
```
JWT will be created on POST and updated on PATCH request to the entity you've been created, 
for instance, if you send POST request to ```http://example.com/v1/user``` with the following content:
```json
{
  "data": {
    "type":"user",
    "attributes": {
      "first_name":"Alice",
      "last_name":"Hacker",
      "password":"my123Password"
    }
  }
}
``` 

Response will be similar to: 
```json
{
  "data": {
    "type": "user",
    "id": "7",
    "attributes": {
      "first_name": "Alice",
      "last_name": "Hacker",
      "password": null,
      "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjU4ODljOGY2NzE3YjIifQ.eyJpc3MiOiJsYXJhdmVsLmxvYyIsImF1ZCI6ImxhcmF2ZWwubG9jIiwianRpIjoiNTg4OWM4ZjY3MTdiMiIsImlhdCI6MTQ4NTQyNDg4NiwibmJmIjoxNDg1NDI0OTE2LCJleHAiOjE0ODU0Mjg0ODYsInVpZCI6N30.JnC7OhlUIBoMTlu617q0q2nCQ4SqKh19bXtiHfBeg9o",
      "attributes": null,
      "request": null,
      "query": null,
      "server": null,
      "files": null,
      "cookies": null,
      "headers": null
    },
    "links": {
      "self": "laravel.loc/user/7"
    }
  }
}
```
Note if JWT ```enabled=true```, password will be hashed with ```password_hash``` and saved to password field internally.
Do not bother with ```"password": null,``` attribute it is unset before output for safety.
You can add additional checks on password or other fields ex.: length, strength etc in Model on befor/afterSave events.

An example for JWT refresh - ```http://example.com/v1/user/4```:
 
```json
{
  "data": {
    "type":"user",
    "attributes": {
    	"password":"myPassword123",
    	"jwt":true
    }
  }
}
```
Note that password and jwt set to true are required. 

Response:
```json
{
  "data": {
    "type": "user",
    "id": "4",
    "attributes": {
      "first_name": "Alice",
      "last_name": "Hacker",
      "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjU4ODcwNGU1NTRjNzkifQ.eyJpc3MiOiJsYXJhdmVsLmxvYyIsImF1ZCI6ImxhcmF2ZWwubG9jIiwianRpIjoiNTg4NzA0ZTU1NGM3OSIsImlhdCI6MTQ4NTI0MzYyMSwibmJmIjoxNDg1MjQzNjUxLCJleHAiOjE0ODUyNDcyMjEsInVpZCI6NH0.GD96ewc1dhbpz9grNaE2070Qy30Mqkh3B0VpEb7h3mQ",
      ...
```

Regular request with JWT will look like:
```
http://example.com/v1/article?include=tag&jwt=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiIsImp0aSI6IjU4ODVmYmM0NjUyN2MifQ.eyJpc3MiOiJsYXJhdmVsLmxvYyIsImF1ZCI6ImxhcmF2ZWwubG9jIiwianRpIjoiNTg4NWZiYzQ2NTI3YyIsImlhdCI6MTQ4NTE3NTc0OCwibmJmIjoxNDg1MTc1ODA4LCJleHAiOjE0ODUxNzkzNDgsInVpZCI6M30.js5_Fe5tFDfeK88KJJpSEpVO6rYBOG0UFAaVvlYYxcw
```
 
The algorithm to sign the token is HS256, it can be changed in future releases 
with additional user-defined options to let developers choose another. 
However, HMAC SHA-256 is the most popular these days. 

### Caching
API ships with caching ability (via Redis) out of the box, the only thing you need to do is to declare cache settings:
```yaml
  Redis:
    type: object
```
and set the `cache` property in any custom entity, for instance: 
```yaml
  Article:
    type: object
    properties:
      ...
      cache:
        type: Redis
        properties:
          stampede_xfetch:
            type: boolean
            default: true
          stampede_beta:
            type: number
            default: 1.5
          ttl:
            type: integer
            default: 300
```
one can set multiple instances of Redis servers, if they have clusters or replica-set.

Another option is to make your services resistant to [Cache Stampede](https://en.wikipedia.org/wiki/Cache_stampede) (or dog-piling) by applying 
corresponding stampede properties to `cache` entity, 
`stampede_xfetch` turns on the xfetch implementation and `stampede_beta` should be 0.5<=beta<=2.0 (where > 1.0 schedule a recompute earlier, < 1.0 schedule a recompute later), 
ttl property is also required in this case.

Generated config output will look similar to:
```php
'cache'=> [
    'article'=> [
        'enabled' => true,
        'stampede_xfetch' => true,
        'stampede_beta' => 1.5,
        'ttl' => 300,
    ],
],
``` 

All specific settings including host/port/password, replication, clusters etc can be easily configured via Laravel standard Redis cache settings.
Read more on this here - [Redis Laravel configuration](https://laravel.com/docs/5.6/redis#configuration)

After cache settings configured - `index` and `view` requests (ex.: `/v1/article/1?include=tag&data=["title", "description"]` or `/v1/article?include=tag&filter=...`) 
will put resulting data into cache with hashed key of a specified uri, thus providing a unique key=value storage mechanism.

In Redis db instance you'll see serialized objects with keys like:
```
index:fa006676687269b5d1b12583ac1a8b64
...
view:f2d62a3c2003dcc0d89ef7d6746b6444
```

### Soft Delete

When models are soft deleted, they are not actually removed from your database. 
Instead, a `deleted_at` attribute is set on the model and inserted into the database. 
If a model has a non-null `deleted_at` value, the model has been soft deleted.
 
To enable soft deletes for a model just add `deleted_at` property on any custom type you need, ex.:
```yaml
  ArticleAttributes:
    description: Article attributes description
    type: object
    properties:
      ...
      deleted_at:
        type: datetime    
``` 

Special generated properties/traits will appear for the specified types in ```Entities/``` folder, also related migration field will be created.

Model example:
```php
<?php
namespace Modules\V2\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use SoliDry\Extension\BaseModel;

class Article extends BaseModel 
{
    use SoftDeletes;

    // >>>props>>>
    protected $dates = ['deleted_at'];
    // ...
}
```

Migration example:
```php
<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration 
{
    public function up() 
    {
        Schema::create('article', function(Blueprint $table) {
            // ...
            $table->softDeletes();
            // ...
        });
    }
    // ...
}
```

It will be then automatically applied for delete requests and models won't be collected for view/index.  

### Turn off JSON API support
If you are willing to disable json api specification mappings into Laravel application (for instance - you need to generate MVC-structure into laravel-module and make your own json schema, or any other output format), just set ```$jsonApi``` property in DefaultController to false:
```php
<?php
namespace Modules\V1\Http\Controllers;

use SoliDry\Extension\BaseController;

class DefaultController extends BaseController 
{
    protected $jsonApi = false;
}
```
As this class inherited by all Controllers - you don't have to add this property in every Controller class.
By default JSON API is turned on.

### Tree structures

You can easily build a tree structure by declaring it as `Trees` custom type:   
```yaml
  Trees:
    type: object
    properties:
      menu:
        type: boolean
        default: true
      catalog:
        type: boolean
        default: false
```

and adding `parent_id` to the targeted table, ex.:
```yaml
  MenuAttributes:
    type: object
    properties:
      title:
        required: true
        type: string
      rfc:
        type: string
        default: /
      parent_id:
        description: mandatory field for building trees
        type: integer
        minimum: 9
        maximum: 10
        default: 0
```
the entire tree will be placed in `meta` json-api root element, 
while all the parent elements (stored as parent_id=0) will reside in data root element.
This was done to keep steady json-api structure and it's relations.

Meta data response example: 
```json
  "meta": {
    "menu_tree": [
      {
        "id": 1,
        "title": "ttl1",
        "rfc": "/",
        "parent_id": 0,
        "created_at": null,
        "updated_at": null,
        "children": [
          {
            "id": 3,
            "title": "ttl21",
            "rfc": "/",
            "parent_id": 1,
            "created_at": null,
            "updated_at": null,
            "children": []
          },
          {
            "id": 2,
            "title": "ttl2",
            "rfc": "/",
            "parent_id": 1,
            "created_at": null,
            "updated_at": null,
            "children": [
              {
                "id": 4,
                "title": "ttl3",
                "rfc": "/",
                "parent_id": 2,
                "created_at": null,
                "updated_at": null,
                "children": []
              }
            ]
          }
        ]
      }
    ]
  }
```

Children elements stuck in every parent's `children` property array and it is empty if there are none.      

To get a sub-trees of a top most ancestors - simply execute GET request for the item, ex.: `http://example.com/v1/menu/1`.
See wiki page for real-world examples with Postman.

### Finite-state machine

To add finite-state machine to a field(column) of an entity(table) - add definition into your OAS file like this:
```yaml
      status:
        description: The state of an article
        enum: ["draft", "published", "postponed", "archived"]
        facets:
          state_machine:
            initial: ['draft']
            draft: ['published']
            published: ['archived', 'postponed']
            postponed: ['published', 'archived']
            archived: []
```
The only required particular item in `state_machine` declaration is an `initial` value of state machine.

After generation process will pass, you'll get the following content in `config.php`:
```php
    'state_machine'=> [
        'article'=> [
            'status'=> [
                'enabled'=>true,
                'states'=> [
                    'initial' => ['draft'],
                    'draft' => ['published'],
                    'published' => ['archived', 'postponed'],
                    'postponed' => ['published', 'archived'],
                    'archived' => [''],
                ],
            ],
        ],
    ],
```
It will be processed on `POST` and `PATCH` requests respectively. 
You can easily disable state machine by setting `enabled` to `false`. 
There is an ability to add state machines in different tables.

### Spell check

#### Installation
The spell checking functionality provided by robust and versatile linux library `GNU aspell` 
and it's dictionaries as extension for PHP.
 
To install an extension for Linux (ex.: Ubuntu):
```zsh
apt-get install php-pspell
```

To install an additional language db run: 
```zsh
apt-get install aspell-fr
```

#### Usage 
You may want to set spell check on particular field/column: 
```yaml
      description:
        required: true
        type: string
        minLength: 32
        maxLength: 1024
        facets:
          spell_check: true
          spell_language: en
```

Generator output in `Modules/{VersionName}/Config/config.php` will look like this:
```php
    'spell_check'=> [
        'article'=> [
            'description'=> [
                'enabled'=>true,
                'language' => 'en',
            ],
        ],
    ],
```
As in other settings - spell check can be disabled with `enabled` set to false. 
If there is no info preset about language - the `en` will be used as default value.

In responses from methods POST/PATCH (create/update) 
you'll get the `meta` content back with filled array of failed checks in it:
```json
{
  "data": {
    "type": "article",
    "id": "21",
    "attributes": {
      "title": "Quick brown fox",
      "description": "The quick brovn fox jumped ower the lazy dogg",
      "url": "http://example.com/articles/21/tags",
      "show_in_top": "0",
      "status": "draft"
    },
    "links": {
      "self": "example.com/article/21"
    }
  },
  "meta": {
    "spell_check": {
      "description": [
        "brovn",
        "ower",
        "dogg"
      ]
    }
  }
}
```   

### Bit Mask
To use bit mask with automatic flags fragmentation/defragmentation 
you can define additional facets to an integer field like this:
```yaml
  permissions:
    type: integer
    required: false
    maximum: 20
    facets:
      bit_mask:
        publisher: 1
        editor: 2
        manager: 4
        photo_reporter: 8
        admin: 16
```
thus the config entity `bit_mask` will be generated and used on runtime within requests to process data.

Generated config snippet: 
```php
'bit_mask'=> [
    'user'=> [
        'permissions'=> [
            'enabled' => true,
            'flags'=> [
                'publisher' => 1,
                'editor' => 2,
                'manager' => 4,
                'photo_reporter' => 8,
                'admin' => 16,
            ],
        ],
    ],
],
```

And the request/response will be:
```json
{
  "data": {
    "type":"user",
    "attributes": {
        "publisher": false,
        "editor": true,
        "manager": false,
        "photo_reporter": true,
        "admin": true    	
    }
  }
}
```

```json
{
  "data": {
    "type": "user",
    "id": "1",
    "attributes": {
        "first_name": "Alice",
        "last_name": "Hacker",
        "permissions": 26,
        "publisher": false,
        "editor": true,
        "manager": false,
        "photo_reporter": true,
        "admin": true,
```
Recall that U can always hide ex.: `permissions` field in index/view GET requests if U'd like.

### Custom SQL
If by any reason you need to use custom sql query - just define it in `Modules/V1/Config/config.php`:
```php
    'custom_sql'    => [
        'article' => [
            'enabled' => true,
            'query'   => 'SELECT id, title FROM article a INNER JOIN tag_article ta ON ta.article_id=a.id 
                          WHERE ta.tag_id IN (
                          SELECT id FROM tag WHERE CHAR_LENGTH(title) > :tag_len
                          ) ORDER BY a.id DESC',
            'bindings' => [
                'tag_len' => 5,
            ]
        ],
    ],  
```  
as U can see there are `query`, `bindings` (where has been passed a secured param-bound values) and `enabled` parameters for desired entity.
Custom sql query will be executed only for `index` API method, 
so if U need ex. `delete` or `update` specific extra rows - call those methods with previously selected ids.  
  
Don't forget to add Laravel specific `$fillable` or `$guarded` array to let fill-in the object ([mass-assignment rule](https://laravel.com/docs/5.4/eloquent#mass-assignment)) ex.::
```php
    protected $fillable = [
        'id',
        'title'
    ];
```
Note: you need an `id` field to be present, because of json-api serializer.

### Custom business logic

You can add any business logic you need, the best place for your custom-code is in pre-generated controllers ex.: 
to add specific sanitizers on fields for `ArticleController` and modified output you can override `create` method like this:  
```php
<?php
namespace Modules\V1\Http\Controllers;

use Illuminate\Http\Request;

class ArticleController extends DefaultController
{
    public function create(Request $request)
    {
        // any business logic here for input pre-processing data
        parent::create($request);
        // any business logic here for output pre-processing data
    }
}
```
There can be situations where you need to add workaround in particular method or init logic for all requests of that type index/view/create/update/delete, 
it can be easily achieved by placing code in `DefaultController` the same way it is for any other Controllers. The inheritance model made specifically for 
those purposes will gracefully perform any ops before/after etc. For instance: 
```php
<?php
namespace Modules\V1\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use SoliDry\Extension\BaseController;

class DefaultController extends BaseController 
{
    public function __construct(Route $route)
    {
        // specific code before init 
        parent::__construct($route);
        // specific code after init 
    }
    
    public function index(Request $request)
    {
        // specific code before index execution
        parent::index($request); 
        // specific code after index execution
    }
}
```     
As u may noticed there is an access to either Route and Request properties.    
In next chapter you'll know how to place custom code in Models/FormRequest preserving it from code regeneration override.   
  
### Regeneration
It is an important feature to regenerate your code based on generated history types and the current state 
of OpenApi document, which can be easily achieved by running: 
```sh  
  php artisan api:generate oas/openapi.yaml --migrations --regenerate --merge=last
```  
This command will merge the last state/snapshot of document from `.gen` directory 
and the current document (in this case from `oas/openapi.yaml`), 
then creates files for models and FormRequest, merging it with user added content between generated props and methods.
Moreover, it will add the new columns to newly created migration files with their indices.

Controller state:
```php
<?php
namespace Modules\V2\Http\Controllers;

class ArticleController extends DefaultController 
{
    private $prop = 'foo';

    // >>>props>>>
    // <<<props<<<
    
    public function myMethod()
    {
        return true;
    }
}
```
 
Example of regenerated FormRequest:
```php
<?php
namespace Modules\V2\Http\Requests;

use SoliDry\Extension\BaseFormRequest;

class TagFormRequest extends BaseFormRequest
{
    public $userPropOne = true;
    // >>>props>>>
    public $id = null;
    // Attributes
    public $title = null;
    // <<<props<<<
    public $userPropTwo = 123;


    public function userDefinedMethod(): int
    {
        return 1;
    }

    // >>>methods>>>
    public function authorize(): bool 
    {
        return true;
    }

    public function rules(): array 
    {
        return [
            "title" => "string|required|min:3",
        ];
    }

    public function relations(): array 
    {
        return [
            "article",
        ];
    }
    // <<<methods<<<

    public function anotherUserDefinedMethod(): bool
    {
        return false;
    }
}
```
As you can see all user content was preserved and merged with regenerated. 
Custom business logic content saves it's state when `--regenerate` option is present, either with or without other options. 
 
The same is true for Eloquent model:
```php
<?php
namespace Modules\V1\Entities;

use SoliDry\Extension\BaseModel;

class Article extends BaseModel 
{
    public $userPropOne = true;
    // >>>props>>>
    protected $primaryKey = "id";
    protected $table = "article";
    public $timestamps = false;
    // <<<props<<<
    public $userPropTwo = 123;

    public function userDefinedMethod(): int
    {
        return 1;
    }

    // >>>methods>>>

    public function tag() 
    {
        return $this->belongsToMany(Tag::class, 'tag_article');
    }
    public function topic() 
    {
        return $this->belongsTo(Topic::class);
    }
    // <<<methods<<<

    public function anotherUserDefinedMethod(): bool
    {
        return false;
    }
} 
```

Example of regenerated migration:
```php
<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnLastNameToUser extends Migration 
{
    public function up() 
    {
        Schema::table('user', function(Blueprint $table) {
            $table->string('last_name', 256);
            $table->index(['first_name', 'last_name']);
            $table->unsignedBigInteger('permissions');
        });
    }

    public function down() 
    {
        Schema::table('user', function(Blueprint $table) {
            $table->dropColumn('last_name');
            $table->dropColumn('permissions');
        });
    }

}
``` 
If you don't want to save history on every run add `--no-history` option.  

There are also more things you can do, about rewinding history: 
- by passing option like this `--merge=9` generator will get back for 9 steps 
- `--merge="2017-07-29 11:35:32"` generator gets to the concrete files by time in history

Although, if you need to totally rollback the state of a system - use `--rollback` option, with the same keys as in merge. 

==== Infection code coverage ====

Metrics:

     Mutation Score Indicator (MSI): 81%
     Mutation Code Coverage: 86%
     Covered Code MSI: 93%
         
==========

HTTP request/response examples can be found on WiKi page - https://github.com/SoliDry/api-generator/wiki

Laravel project example with generated files can be found here -  https://github.com/SoliDry/rjapi-laravel 

To get deep-into ```Open API``` specification - https://swagger.io/specification/

To get deep-into ```JSON-API``` specification - http://jsonapi.org/format/
JSON-API support is provided, particularly for output, by Fractal package - http://fractal.thephpleague.com/

Happy coding ;-)

PS The purpose of this repo is to prevent doing the same things over and over again, expecting different results. (Thx to Albert Einstein) 