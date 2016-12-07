<?php
namespace rjapi\extension\yii2\raml\controllers;

use Raml\Schema\Parser\JsonSchemaParser;
use rjapi\extension\yii2\raml\blocks\Controllers;
use rjapi\extension\yii2\raml\blocks\InModels;
use rjapi\extension\yii2\raml\blocks\Module;
use rjapi\extension\yii2\raml\blocks\OutModels;
use rjapi\extension\yii2\raml\exception\AttributesException;
use rjapi\extension\yii2\raml\exception\DirectoryException;
use rjapi\extension\yii2\raml\exception\SchemaException;
use yii\console\Controller;
use Raml\Parser;

class SchemaController extends Controller
{
    const PHP_OPEN_TAG  = '<?php';
    const PHP_EXT       = '.php';
    const PHP_EXTENDS   = 'extends';
    const PHP_NAMESPACE = 'namespace';
    const PHP_CLASS     = 'class';
    const PHP_USE       = 'use';

    const DEFAULT_POSTFIX = 'Controller';
    const FORM_BASE       = 'Base';
    const FORM_PREFIX     = 'Form';
    const FORM_ACTION     = 'Action';
    const FORM_IN         = 'In';
    const FORM_OUT        = 'Out';

    const DEFAULT_MODULE     = 'Module';
    const DEFAULT_CONTROLLER = 'BaseMapperController';
    const DEFAULT_MODEL_IN   = 'BaseResourceFormIn';
    const DEFAULT_MODEL_OUT  = 'BaseResourceFormOut';

    // RAML types
    const RAML_TYPE_ARRAY  = 'array';
    const RAML_TYPE_OBJECT = 'object';
    const RAML_PROPS       = 'properties';

    const CONTENT_TYPE = 'application/vnd.api+json';

    const OPEN_BRACE  = '{';
    const CLOSE_BRACE = '}';

    const OPEN_BRACKET  = '[';
    const CLOSE_BRACKET = ']';

    const TAB_PSR4    = "    ";
    const COLON       = ':';
    const SEMICOLON   = ';';
    const DOLLAR_SIGN = '$';
    const SLASH       = '/';
    const BACKSLASH   = '\\';
    const EQUALS      = '=';
    const SPACE       = ' ';

    const PHP_TYPES_ARRAY = 'array';
    const PHP_TYPES_NULL  = 'null';

    const DIR_MODE = '0755';

    const RESPONSE_CODE_200 = '200';
    const RESPONSE_CODE_201 = '201';

    public $rootDir = '';

    public $appDir         = 'app';
    public $modulesDir     = 'modules';
    public $controllersDir = 'controllers';
    public $modelsFormDir  = 'models';
    public $formsDir       = 'forms';

    public $version;
    public $controller        = null;
    public $defaultController = 'Default';
    public $uriNamedParams    = null;

    public $ramlFile = '';
    public $force    = null;

    private $outModels    = null;
    private $inModels     = null;
    private $controllers  = null;
    private $moduleObject = null;

    const HTTP_METHOD_GET    = 'get';
    const HTTP_METHOD_POST   = 'post';
    const HTTP_METHOD_PATCH  = 'patch';
    const HTTP_METHOD_DELETE = 'delete';

    public $actions = [
        self::HTTP_METHOD_GET    => 'View',
        self::HTTP_METHOD_POST   => 'Create',
        self::HTTP_METHOD_DELETE => 'Delete',
        self::HTTP_METHOD_PATCH  => 'Update',
    ];

    /**
     * @param string $actionId the action id of the current request
     *
     * @return array
     */
    public function options($actionId)
    {
        return ['force', 'ramlFile'];
    }

    /**
     * @return array
     */
    public function optionAliases()
    {
        return [
            'f'  => 'force', // force override files
            'rf' => 'ramlFile' // pass RAML file
        ];
    }

    /**
     *  Generates api Controllers + Models to support RAML validation
     */
    public function actionIndex($ramlFile)
    {
        $schemaParser = new JsonSchemaParser();
        $schemaParser->addCompatibleContentType(self::CONTENT_TYPE);

        $parser = new Parser();
        $parser->addSchemaParser($schemaParser);

        $ramlApi = $parser->parse($ramlFile, true);
        /** @var \Raml\Resource $ramlResources */
        $ramlResources = $ramlApi->getResources();

        $this->version = str_replace('/', '', $ramlApi->getVersion());

        $this->createDirs();
        $this->generateResources($ramlResources);
    }

    /**
     * @param \Raml\Resource[] $ramlResources
     */
    private function generateResources($ramlResources)
    {
        $this->controllers = new Controllers($this);
        $this->controllers->createDefault();

        $this->moduleObject = new Module($this);
        $this->moduleObject->createModule();

        $this->inModels  = new InModels($this);
        $this->outModels = new OutModels($this);

        foreach($ramlResources as $resource)
        {
            $this->controller = ucfirst(explode('/', $resource->getUri())[1]);

            $this->uriNamedParams = $resource->getUriParameters();

            // creating controller
            $this->controllers->setCodeState($this);
            $this->controllers->create();

            $methods = $resource->getMethods();

            // create In Form Model
            $this->inModels->setCodeState($this);
            $this->inModels->createFormModel($methods);

            // create Out Form Model
            $this->outModels->setCodeState($this);
            $this->outModels->createFormModel($methods);
        }
    }

    public function getMethodProperties($bodies, $related = false)
    {
        $attributes = null;

        if(empty($bodies[self::CONTENT_TYPE]))
        {
//            throw new SchemaException('There is no schema defined.');
            return $attributes;
        }
        $jsonBodyArr = $bodies[self::CONTENT_TYPE]->getSchema()->getJsonArray();

        if(empty($jsonBodyArr[self::RAML_PROPS]['data']['type']))
        {
            return $attributes;
        }

        if($related === true) // parse relations
        {
            if($jsonBodyArr[self::RAML_PROPS]['data']['type'] === self::RAML_TYPE_OBJECT
               && !empty($jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['relationships'][self::RAML_PROPS])
            )
            {
                $attributes = $jsonBodyArr[self::RAML_PROPS]['data'][self::RAML_PROPS]['relationships'][self::RAML_PROPS];
            }

            if($jsonBodyArr[self::RAML_PROPS]['data']['type'] === self::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['relationships'][self::RAML_PROPS]))
                {
                    $attributes = $jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['relationships'][self::RAML_PROPS];
                }
                if(!empty($jsonBodyArr[self::RAML_PROPS]['data']['items'][self::RAML_PROPS]))
                {
                    $attributes = $jsonBodyArr[self::RAML_PROPS]['data']['items'][self::RAML_PROPS];
                }
            }
        }
        else
        {// parse attributes
            if($jsonBodyArr[self::RAML_PROPS]['data']['type'] === self::RAML_TYPE_OBJECT)
            {
                $attributes       = $jsonBodyArr[self::RAML_PROPS]['data'][self::RAML_PROPS]['attributes'][self::RAML_PROPS];
                $attributes['id'] = $jsonBodyArr[self::RAML_PROPS]['data'][self::RAML_PROPS]['id'];
            }

            if($jsonBodyArr[self::RAML_PROPS]['data']['type'] === self::RAML_TYPE_ARRAY)
            {
                if(!empty($jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['attributes'][self::RAML_PROPS]))
                {
                    $attributes       = $jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['attributes'][self::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['id'];
                }
                if(!empty($jsonBodyArr[self::RAML_PROPS]['data']['items'][self::RAML_PROPS]))
                {
                    $attributes       = $jsonBodyArr[self::RAML_PROPS]['data']['items'][self::RAML_PROPS];
                    $attributes['id'] = $jsonBodyArr[self::RAML_PROPS]['data']['items'][0]['id'];
                }
            }
        }

        if($attributes === null)
        {
            throw new AttributesException('There hasn`t been set or not set correctly neither "attributes" nor "relationships" tag in schema.');
        }

        return $attributes;
    }

    private function createDirs()
    {
        // create modules dir
        if(is_dir($this->rootDir . $this->modulesDir) === false)
        {
            if(mkdir($this->rootDir . $this->modulesDir, self::DIR_MODE) === false)
            {
                throw new DirectoryException('Couldn`t create directory ' . $this->rootDir . $this->modulesDir . ' with ' . self::DIR_MODE . ' mode.');
            }
            chmod($this->rootDir . $this->modulesDir, self::DIR_MODE);
        }
        // create version dir
        if(is_dir($this->rootDir . $this->modulesDir . self::SLASH . $this->version) === false)
        {
            if(mkdir($this->rootDir . $this->modulesDir . self::SLASH . $this->version, self::DIR_MODE) === false)
            {
                throw new DirectoryException('Couldn`t create directory ' . $this->rootDir . $this->modulesDir . self::SLASH . $this->version . ' with ' . self::DIR_MODE . ' mode.');
            }
            chmod($this->rootDir . $this->modulesDir . self::SLASH . $this->version, self::DIR_MODE);
        }
        // create controllers dir
        if(is_dir($this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->controllersDir) === false)
        {
            if(mkdir($this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->controllersDir, self::DIR_MODE) === false)
            {
                throw new DirectoryException('Couldn`t create directory ' . $this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->controllersDir . ' with ' . self::DIR_MODE . ' mode.');
            }
            chmod($this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->controllersDir, self::DIR_MODE);
        }
        // create forms dir
        if(is_dir(
               $this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->modelsFormDir
               . self::SLASH . $this->formsDir
           ) === false
        )
        {
            if(mkdir(
                   $this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->modelsFormDir
                   . self::SLASH . $this->formsDir, self::DIR_MODE, true
               ) === false
            )
            {
                throw new DirectoryException('Couldn`t create directory ' . $this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->controllersDir . ' with ' . self::DIR_MODE . ' mode.');
            }
            chmod($this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->modelsFormDir, self::DIR_MODE);
            chmod(
                $this->rootDir . $this->modulesDir . self::SLASH . $this->version . self::SLASH . $this->modelsFormDir
                . self::SLASH . $this->formsDir, self::DIR_MODE
            );
        }
    }
}