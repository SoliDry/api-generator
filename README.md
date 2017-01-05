# raml-json-api
[![Build Status](https://scrutinizer-ci.com/g/RJAPI/raml-json-api/badges/build.png?b=master)](https://scrutinizer-ci.com/g/RJAPI/raml-json-api/build-status/master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/RJAPI/raml-json-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/RJAPI/raml-json-api/?branch=master)
[![MIT Licence](https://badges.frapsoft.com/os/mit/mit.svg?v=103)](https://opensource.org/licenses/mit-license.php)

RAML-JSON-API PHP-code generator (based on RAML-types) for Laravel framework, with complete support of JSON-API data format 

JSON API support turned on by default - see `Turn off JSON API support` section bellow 

### Installation via composer:
``` 
composer require rjapi/raml-json-api 
```

### Laravel specific configuration

Add command to ```$commands``` array in ```app/Console/Kernel.php```
```php
protected $commands = [
    RJApiGenerator::class,
];
```

Run in console:
```
php artisan raml:generate raml/articles.raml --migrations
```

This command creates the whole environment for You to proceed building complex API based on RAML/Laravel/JSON API, 
in particular it creates: directories for modular app, Controllers/Middlewares/Models+Pivots to support full MVC, 
Routes (JSON API compatible) and even migrations to help You create RDBMS structure.
 
```raml/articles.raml``` - raml file in raml directory in the root of Your project, 
which should be prepared before or You may wish to just try by copying an example from ``` tests/functional/articles.raml```

The output will look something like this:
```
Created : /srv/rjapi-laravel/Modules/V1/start.php
Created : /srv/rjapi-laravel/Modules/V1/Http/routes.php
Created : /srv/rjapi-laravel/Modules/V1/module.json
Created : /srv/rjapi-laravel/Modules/V1/Resources/views/index.blade.php
Created : /srv/rjapi-laravel/Modules/V1/Resources/views/layouts/master.blade.php
Created : /srv/rjapi-laravel/Modules/V1/Config/config.php
Created : /srv/rjapi-laravel/Modules/V1/composer.json
Created : /srv/rjapi-laravel/Modules/V1/Database/Seeders/V1DatabaseSeeder.php
Created : /srv/rjapi-laravel/Modules/V1/Providers/V1ServiceProvider.php
Created : /srv/rjapi-laravel/Modules/V1/Http/Controllers/V1Controller.php
Module [V1] created successfully.
Module [V1] used successfully.
```
This is done (behind the scene) by wonderful package laravel-modules, 
many thx to nWidart https://github.com/nWidart/laravel-modules 

And RAML-types based generated files:
```sh
================ Article Entities
Modules/V1/Http/Controllers/DefaultController.php created
Modules/V1/Http/Controllers/ArticleController.php created
Modules/V1/Http/Middleware/ArticleMiddleware.php created
Modules/V1/Entities/ArticleTag.php created
Modules/V1/Entities/Article.php created
Modules/V1/Database/Migrations/03_01_2017_132841_create_article_table.php created
Modules/V1/Database/Migrations/03_01_2017_132857_create_article_tag_table.php created
================ Tag Entities
Modules/V1/Http/Controllers/TagController.php created
Modules/V1/Http/Middleware/TagMiddleware.php created
Modules/V1/Entities/Tag.php created
Modules/V1/Database/Migrations/03_01_2017_132895_create_tag_table.php created
...
```

The dynamic module name similar to: v1, v2 - will be taken on runtime 
as the last element of the array in ```config/module.php``` file, 
if You, by strange circumstances, want to use one of the previous modules, 
just set one of previously registered modules as the last element of an array.  

Generated migrations will look like standard migrations in Laravel:
```php
<?php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration 
{
    public function up() {
        Schema::create('article', function(Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('description');
            $table->string('url');
            // Show at the top of main page
            $table->unsignedTinyInteger('show_in_top');
            // ManyToOne Topic relationship
            $table->integer('topic_id');
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('article');
    }

}
```

Note that all migrations for specific module will be placed in ``` Modules/{ModuleName}/Database/Migrations/ ```

To execute them all - run: ``` php artisan module:migrate ```

### RAML Types and Declarations

The ```version``` root property !required
```RAML
version: v1
```
converts to ```/Modules/V1/``` directory.

Types ``` ID, Type, DataObject/DataArray``` are special helper types - !required
```RAML
  ID:
    type: integer
    required: true
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
```RAML
  RelationshipsDataItem:
    type: object
    properties:
      id: ID
      type: Type
```
defined in every relationship custom type

Attributes ```*Attributes``` are defined for every custom Object ex.:
```RAML
  ArticleAttributes:
    description: Article attributes description
    type: object
    properties:
      title:
        required: true
        type: string
        minLength: 16
        maxLength: 256
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
      show_in_top:
        description: Show at the top of main page
        required: false
        type: boolean
      status:
        description: The state of an article
        enum: ["draft", "published", "postponed", "archived"]
```

Relationships custom type definition semantics ```*Relationships```
```RAML
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
```RAML
  Article:
    type: object
    properties:
      type: Type
      id: ID
      attributes: ArticleAttributes
      relationships:
        type: TagRelationships[] | TopicRelationships
```
That is all that PHP-code generator needs to provide code structure that just works out-fo-the-box within Laravel framework, 
where may any business logic be applied

Complete directory structure after generator will end up it`s work will be like:
```php
Modules/{ModuleName}/Http/Controllers/ - contains controllers that extends the DefaultController (descendant of Laravel's Controller)
Modules/{ModuleName}/Http/Middleware/ - contains forms that extends the BaseFormRequest (descendant of Laravel's FormRequest) and validates input attributes (that were previously defined as *Attributes in RAML)
Modules/{ModuleName}/Entities/ - contains mappers that extends the BaseModel (descendant of Laravel's Model) and maps attributes to RDBMS
Modules/{ModuleName}/Http/routes.php - contains routings pointing to controllers with JSON API protocol support
Modules/{ModuleName}/Database/Migrations/ - contains migrations created with option --migrations
```

DefaultController example:
```php
<?php
namespace Modules\V1\Http\Controllers;

class ArticleController extends DefaultController 
{

}
```
By default every controller works with any of GET - index/view, POST - create, PATCH - update, DELETE - delete methods.
So You don't need to implement anything special here.

Validation BaseFormRequest example:
```php
<?php
namespace Modules\V1\Http\Middleware;

use rjapi\extension\BaseFormRequest;

class ArticleMiddleware extends BaseFormRequest 
{
    public $id = null;
    // Attributes
    public $title = null;
    public $description = null;
    public $url = null;
    public $show_in_top = null;
    public $status = null;
    public $topic_id = null;

    public function authorize(): bool {
        return true;
    }

    public function rules(): array {
        return [
            "title" => "required|string|min:16|max:256",
            "description" => "required|string|min:32|max:1024",
            "url" => "string|min:16|max:255",
            // Show at the top of main page
            "show_in_top" => "boolean",
            // The state of an article
            "status" => "in:draft,published,postponed,archived",
            // ManyToOne Topic relationship
            "topic_id" => "required|integer|min:1|max:9",
        ];
    }

    public function relations(): array {
        return [
            "tag",
            "topic",
        ];
    }

}
```

BaseModel example:
```php
<?php
namespace Modules\V1\Entities;

use rjapi\extension\BaseModel;

class Article extends BaseModel 
{
    protected $primaryKey = "id";
    protected $table = "article";
    public $timestamps = false;

    public function tag() {
        return $this->belongsToMany(Tag::class, 'tag_article');
    }

    public function topic() {
        return $this->belongsTo(Topic::class);
    }

}
```

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
This way You telling to generator: "make the relation between Article and Tag OneToMany from Article to Tag"
The idea works with any relationship You need - ex. ManyToMany: ```TagRelationships[] -> ArticleRelationships[]```, 
OneToOne: ```TagRelationships -> ArticleRelationships```

You can also bind several relationships to one entity, for instance - 
You have an Article entity that must be bound to TagRelationships and TopicRelationships, this mey be done similar to:
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

### Turn off JSON API support
If you are willing to disable json api specification mappings into Laravel application (for instance - You need to generate MVC-structure into laravel-module and make Your own json schema, or any other output format), just set ```$jsonApi``` property in DefaultController to false:
```php
<?php
namespace Modules\V1\Http\Controllers;

use rjapi\extension\BaseController;

class DefaultController extends BaseController 
{
    protected $jsonApi = false;
}
```
As this class inherited by all Controllers - You don't have to add this property in every Controller class.
By default JSON API is turned on.

HTTP request/response examples can be found on WiKi page - https://github.com/RJAPI/raml-json-api/wiki

Laravel project example with generated files can be found here -  https://github.com/RJAPI/rjapi-laravel 

To get deep-into ```RAML``` specification - https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/

To get deep-into ```JSON-API``` specification - http://jsonapi.org/format/
JSON-API support is provided, particularly for output, by Fractal package - http://fractal.thephpleague.com/

Happy coding ;-)