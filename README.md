# raml-json-api
RAML-JSON-API PHP-code generator for different FrameWorks aka Laravel/Yii/Symfony etc

## Yii2 Configuration

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
creates dir/file structure for Yii2 FrameWork

Types ``` ID, Type, Data``` are special helper types
```RAML
  ID:
    type: integer
    required: true
  Type:
    type: string
    required: true
    minLength: 1
    maxLength: 255
  Data:
    type: object
    required: true
```