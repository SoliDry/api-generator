<?php
namespace rjapi\blocks;

use rjapi\extension\BaseFormRequest;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Console;
use rjapi\helpers\MethodOptions;
use rjapi\RJApiGenerator;
use rjapi\helpers\Classes;
use rjapi\types\ConfigInterface;
use rjapi\types\CustomsInterface;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\HTTPMethodsInterface;
use rjapi\types\MethodsInterface;
use rjapi\types\MiddlewareInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class Middleware
 * @package rjapi\blocks
 * @property RJApiGenerator generator
 */
class Middleware extends FormRequestModel
{
    use ContentManager;

    protected $sourceCode = '';
    protected $generator = null;
    private $additionalProps = [
        'id' => [
            'type' => 'integer',
        ],
    ];
    private $className = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setContent();
        $fileForm = $this->generator->formatMiddlewarePath()
            . PhpInterface::SLASH
            . $this->className
            . DefaultInterface::MIDDLEWARE_POSTFIX
            . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile(
            $fileForm, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($fileForm . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setProps($relationTypes = null)
    {
        $this->setAdditionalProps();
        // properties creation
        $this->setPropsContent();
        // related props
        $this->setRelationTypes($relationTypes);
    }

    private function setAdditionalProps()
    {
        // additional props
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->createProperty($prop, PhpInterface::PHP_MODIFIER_PUBLIC);
            }
        }
    }

    private function setPropsContent()
    {
        $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::COMMENT . ' Attributes' . PHP_EOL;
        foreach($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
                [RamlInterface::RAML_PROPS] as $propKey => $propVal)
        {
            if(is_array($propVal))
            {
                $this->createProperty($propKey, PhpInterface::PHP_MODIFIER_PUBLIC);
            }
        }
        $this->sourceCode .= PHP_EOL;
    }

    private function setRelationTypes($relationTypes)
    {
        // related props
        if($relationTypes !== null)
        {
            $this->sourceCode .= PhpInterface::TAB_PSR4 . PhpInterface::COMMENT . ' Relations' .
                PHP_EOL;
            foreach($relationTypes as $attrKey => $attrVal)
            {
                // determine attr
                if($attrKey !== RamlInterface::RAML_ID && $attrKey !== RamlInterface::RAML_TYPE)
                {
                    $this->createProperty($attrKey, PhpInterface::PHP_MODIFIER_PUBLIC);
                }
            }
            $this->sourceCode .= PHP_EOL;
        }
    }

    private function constructRules()
    {
        // Authorize method - defaults to false
//        $this->startMethod(PhpEntitiesInterface::PHP_AUTHORIZE, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_BOOL);
        $methodOptions = new MethodOptions();
        $methodOptions->setName(PhpInterface::PHP_AUTHORIZE);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_BOOL);
        $this->startMethod($methodOptions);
        $this->setMethodReturn(PhpInterface::PHP_TYPES_BOOL_TRUE);
        $this->endMethod();

        // Rules method
        $methodOptions->setName(PhpInterface::PHP_RULES);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_ARRAY);
        $this->startMethod($methodOptions);
        // attrs validation
        $this->startArray();
        // gather types and constraints
        $this->setPropertyFilters();
        $this->endArray();
        $this->endMethod();
    }

    private function constructRelations($relationTypes)
    {
        $methodOptions = new MethodOptions();
        $methodOptions->setName(MethodsInterface::RELATIONS);
        $methodOptions->setReturnType(PhpInterface::PHP_TYPES_ARRAY);
        $this->startMethod($methodOptions);
        // attrs validation
        $this->startArray();
        if(empty($relationTypes) === false)
        {
            $rel = empty($relationTypes[RamlInterface::RAML_TYPE]) ? $relationTypes :
                $relationTypes[RamlInterface::RAML_TYPE];

            $rels = explode(PhpInterface::PIPE, str_replace('[]', '', $rel));
            foreach($rels as $k => $rel)
            {
                $this->setRelations(strtolower(trim(str_replace(CustomsInterface::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
                if(empty($rels[$k + 1]) === false)
                {
                    $this->sourceCode .= PHP_EOL;
                }
            }
        }
        $this->endArray();
        $this->endMethod();
    }

    private function setRelations($relationTypes)
    {
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::DOUBLE_QUOTES . $relationTypes .
            PhpInterface::DOUBLE_QUOTES
            . PhpInterface::COMMA;
    }

    /**
     *  Sets content of *Middleware
     */
    private function setContent()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir .
            PhpInterface::BACKSLASH .
            $this->generator->middlewareDir
        );

        $baseFullForm = BaseFormRequest::class;
        $baseFormName = Classes::getName($baseFullForm);
        $this->setUse($baseFullForm, false, true);
        $this->startClass($this->className . DefaultInterface::MIDDLEWARE_POSTFIX, $baseFormName);

        if(empty($this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
            &&
            empty($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]]) === false
        )
        {
            $this->setProps(
                $this->generator->types[$this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]]
                [RamlInterface::RAML_PROPS][RamlInterface::RAML_DATA][RamlInterface::RAML_ITEMS]
            );
        }
        else
        {
            $this->setProps();
        }

        $this->constructRules();
        $relTypes = empty($this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE])
            ? [] : $this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE];
        $this->constructRelations($relTypes);

        // create closing brace
        $this->endClass();
    }

    public function createAccessToken()
    {
        if(empty($this->generator->types[CustomsInterface::CUSTOM_TYPES_QUERY_PARAMS][RamlInterface::RAML_PROPS]
            [JSONApiInterface::PARAM_ACCESS_TOKEN][RamlInterface::RAML_KEY_DEFAULT]) === false
        )
        {
            $this->setAccessTokenContent();
            $fileForm = strtolower(DirsInterface::APPLICATION_DIR)
                . PhpInterface::SLASH . $this->generator->httpDir
                . PhpInterface::SLASH . $this->generator->middlewareDir
                . PhpInterface::SLASH . JSONApiInterface::CLASS_API_ACCESS_TOKEN
                . PhpInterface::PHP_EXT;
            $isCreated = FileManager::createFile(
                $fileForm, $this->sourceCode,
                FileManager::isRegenerated($this->generator->options)
            );
            if($isCreated)
            {
                Console::out($fileForm . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
            }
        }
    }

    private function setAccessTokenContent()
    {
        $this->setTag();
        $this->sourceCode .= PhpInterface::PHP_NAMESPACE . PhpInterface::SPACE .
            DirsInterface::APPLICATION_DIR . PhpInterface::BACKSLASH . $this->generator->httpDir .
            PhpInterface::BACKSLASH . $this->generator->middlewareDir
            . PhpInterface::SEMICOLON . PHP_EOL . PHP_EOL;

        $this->setUse(PhpInterface::CLASS_CLOSURE, false, true);
        $this->startClass(JSONApiInterface::CLASS_API_ACCESS_TOKEN);
        $methodOptions = new MethodOptions();
        $methodOptions->setName(MiddlewareInterface::METHOD_HANDLE);
        $methodOptions->setParams([
            MiddlewareInterface::METHOD_PARAM_REQUEST,
            PhpInterface::CLASS_CLOSURE => MiddlewareInterface::METHOD_PARAM_NEXT,
        ]);
        $this->startMethod($methodOptions);
        $this->setHandleMethodContent();
        $this->endMethod();
        $this->endClass();
    }

    private function setHandleMethodContent()
    {
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::IF . PhpInterface::SPACE . PhpInterface::OPEN_PARENTHESES
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::PHP_TYPES_STRING . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SPACE . PhpInterface::DOLLAR_SIGN . MiddlewareInterface::METHOD_PARAM_REQUEST
            . PhpInterface::ARROW . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpInterface::SPACE . PhpInterface::EXCLAMATION
            . PhpInterface::EQUALS . PhpInterface::EQUALS . PhpInterface::SPACE
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::PHP_TYPES_STRING . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SPACE . MethodsInterface::CONFIG . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES
            . $this->generator->version . PhpInterface::DOT . ConfigInterface::QUERY_PARAMS . PhpInterface::DOT
            . JSONApiInterface::PARAM_ACCESS_TOKEN . PhpInterface::QUOTES
            . PhpInterface::CLOSE_PARENTHESES . PhpInterface::CLOSE_PARENTHESES . PHP_EOL;
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::OPEN_BRACE . PHP_EOL;
        // response body
        $this->setTabs(3);
        $this->sourceCode .= MethodsInterface::HEADER . PhpInterface::OPEN_PARENTHESES . PhpInterface::QUOTES
            . HTTPMethodsInterface::HTTP_11 . PhpInterface::SPACE . JSONApiInterface::HTTP_RESPONSE_CODE_ACCESS_FORBIDDEN
            . PhpInterface::SPACE . JSONApiInterface::FORBIDDEN
            . PhpInterface::QUOTES . PhpInterface::CLOSE_PARENTHESES
            . PhpInterface::SEMICOLON . PHP_EOL;
        $this->setTabs(3);
        $this->setEchoString('Access forbidden.');
        $this->setTabs(3);
        $this->sourceCode .= PhpInterface::DIE . PhpInterface::SEMICOLON . PHP_EOL;
        $this->setTabs(2);
        $this->sourceCode .= PhpInterface::CLOSE_BRACE . PHP_EOL . PHP_EOL;
        $this->setMethodReturn(PhpInterface::DOLLAR_SIGN . MiddlewareInterface::METHOD_PARAM_NEXT
            . PhpInterface::OPEN_PARENTHESES . PhpInterface::DOLLAR_SIGN . MiddlewareInterface::METHOD_PARAM_REQUEST
            . PhpInterface::CLOSE_PARENTHESES);

    }
}