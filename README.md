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
converts to ```/modules/v1/``` directory.

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
        type: string
        required: true
        maxLength: 500
      url:
        type: string
        required: true
        maxLength: 255
      meta_title:
        type: string
        required: false
        maxLength: 255
      meta_description:
        type: string
        required: false
        maxLength: 255
      show_menu:
        type: boolean
        required: true
      publish_rss:
        type: boolean
        required: true
      post_aggregator:
        type: boolean
        required: true
      display_tape:
        type: boolean
        required: true
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
modules/{version}/controllers/ - contains controllers that extends the DefaultController
modules/{version}/models/forms/ - contains forms that extends the BaseFormResource and validates input attributes (that were previously defined as *Attributes in RAML)
modules/{version}/models/mappers/ - contains mappers that extends the BaseActiveDataMapper which maps to the Containers and saves data to RDBMS like MySQL,PostgreSQL etc
modules/{version}/models/containers/ - extends ActiveRecord and provides straight access to CRUD
```

Properties of Input json-api parameters - Yii Form generation example:
```php
    // ID
    public $id = null;

    // Attributes
    public $title                  = null;
    public $body                   = null;
    public $lead                   = null;
    public $copyright              = null;
    public $url                    = null;
    public $meta_title             = null;
    public $meta_description       = null;
    public $title_vk_fb            = null;
    public $body_vk_fb             = null;
    public $body_twitter           = null;
    public $fb_image               = null;
    public $vk_image               = null;
    public $view_options           = null;
    public $status                 = null;
    public $show_in_menu           = null;
    public $publish_to_rss         = null;
    public $publish_to_aggregators = null;
    public $show_in_tape           = null;
```

Rules of Input json-api parameters - Yii Form generation example:
```php
    public function rules()
    {
        return [
            ["id", "integer"],
            ["title", "string"],
            ["body", "string"],
            ["lead", "string"],
            ["copyright", "string"],
            ["url", "string"],
            ["meta_title", "string", "min" => "2", "max" => "128"],
            ["meta_description", "string", "min" => "2", "max" => "128"],
            ["body_twitter", "string"],
            ["title_vk_fb", "string", "max" => "255"],
            ["body_vk_fb", "string", "max" => "255"],
            ["fb_image", "string", "max" => "255"],
            ["vk_image", "string", "max" => "255"],
            ['view_options', 'integer', 'max' => '64'],
            ["status" , "in", "range" => ["draft", "published", "postponed", "archived"]],
            ["show_in_menu", "integer", "max" => "1"],
            ["publish_to_rss", "integer", "max" => "1"],
            ["publish_to_aggregators", "integer", "max" => "1"],
            ["show_in_tape", "integer", "max" => "1"],
        ];
    }
```

Relations based on Yii Forms generation example: 
```php
    public function relations(): array {
        return [
            "tags",
            "topics"
        ];
    }
```

### Laravel specific configuration

Add command to ```$commands``` array in ```app/Console/Kernel.php```
```php
    protected $commands = [
        LaravelRJApiGenerator::class,
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