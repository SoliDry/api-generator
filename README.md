# raml-json-api
RAML-JSON-API PHP-code generator for different FrameWorks aka Laravel/Yii/Symfony etc

## RAML Types and Declarations

Use sample RAML file from the root (the same file is in the tests codeception directory)

The ```version``` root property !required
```RAML
version: v1
```
converts to /modules/v1/ directory.

The ```uses``` root property - !required
```RAML
uses:
  FrameWork: yii
```
creates dirs/files structure for specified FrameWork

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
modules/{version}/models/mappers/ - contains ActiveRecord mappers that extends the BaseActiveDataMapper which maps to the Containers and saves data to RDBMS like MySQL,PostgreSQL etc
```