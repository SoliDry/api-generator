# raml-json-api
RAML-JSON-API PHP-code generator (based on RAML-types) for different FrameWorks aka Laravel/Yii/Symfony etc, with complete support of JSON-API data format

### Installation via composer:
``` composer require rjapi/raml-json-api ```

### RAML Types and Declarations

Use sample RAML file from the root (the same file is in the tests codeception directory)

The ```version``` root property !required
```RAML
version: v1
```
converts to ```/Modules/v1/``` directory.

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
  RubricAttributes:
    description: Rubric attributes description
    type: object
    properties:
      name_rubric:
        required: true
        type: string
        minLength: 8
        maxLength: 500
      url:
        required: true
        type: string
        minLength: 16
        maxLength: 255
      meta_title:
        required: false
        type: string
        maxLength: 255
      meta_description:
        required: false
        type: string
        maxLength: 255
      show_menu:
        required: true
        type: boolean
      publish_rss:
        required: true
        type: boolean
      post_aggregator:
        required: true
        type: boolean
      display_tape:
        required: true
        type: boolean
      status:
        description: The state of an article
        enum: ["draft", "published", "postponed", "archived"]
```

Relationships custom type definition semantics ```*Relationships```
```RAML
  TagsRelationships:
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
  Rubric:
    type: object
    properties:
      type: Type
      id: ID
      attributes: RubricAttributes
      relationships: TagsRelationships
```
That is all that PHP-code generator needs to provide code structure that just works out-fo-the-box within Yii2 framework, 
where may any business logic be applied

Complete directory structure after generator will end up it`s work will be like:
```RAML
Modules/{version}/Controllers/ - contains controllers that extends the DefaultController
Modules/{version}/Models/Forms/ - contains forms that extends the BaseFormRequest (parent of Laravel's FormRequest) and validates input attributes (that were previously defined as *Attributes in RAML)
Modules/{version}/Models/Mappers/ - contains mappers that extends the BaseModel (parent of Laravel's Model) and maps attributes to RDBMS
```
DefaultController example:
```php
<?php
namespace App\Modules\v1\Controllers;

class RubricController extends DefaultController 
{

}
```
By default every controller will work with any of GET - index/view, POST - create, PATCH - update, DELETE - delete methods.
So You don't need to implement anything special here.

Validation BaseFormRequest example:
```php
<?php
namespace App\Modules\v1\Models\Forms;

use rjapi\extension\BaseFormRequest;

class BaseFormRubric extends BaseFormRequest 
{
    public $id = null;
    // Attributes
    public $name_rubric = null;
    public $url = null;
    public $meta_title = null;
    public $meta_description = null;
    public $show_menu = null;
    public $publish_rss = null;
    public $post_aggregator = null;
    public $display_tape = null;
    public $status = null;

    public  function authorize(): bool {
        return false;
    }

    public  function rules(): array {
        return [
            "name_rubric" => "required|string|min:8|max:500",
            "url" => "required|string|min:16|max:255",
            "meta_title" => "string|max:255",
            "meta_description" => "string|max:255",
            "show_menu" => "required|boolean",
            "publish_rss" => "required|boolean",
            "post_aggregator" => "required|boolean",
            "display_tape" => "required|boolean",
            "status" => "in:draft,published,postponed,archived",
        ];
    }

    public  function relations(): array {
        return [
            "tags",
        ];
    }
}
```

BaseModel example:
```php
class BaseMapperRubric extends BaseModel 
{
    protected $primaryKey = "id";
    protected $table = "rubric";
    public $timestamps = false;
}
```
### Laravel specific configuration

Add command to ```$commands``` array in ```app/Console/Kernel.php```
```php
    protected $commands = [
        RJApiGenerator::class,
    ];
```

Run in console:
```php
php artisan raml:generate raml/rubric.raml
```
```raml/rubric.raml``` - raml file in raml directory in the root of Your project

Laravel project example with generated files can be found here -  https://github.com/RJAPI/rjapi-laravel 

To get deep-into ```RAML``` specification - https://github.com/raml-org/raml-spec/blob/master/versions/raml-10/raml-10.md/

To get deep-into ```JSON-API``` specification - http://jsonapi.org/format/

Happy coding ;-)