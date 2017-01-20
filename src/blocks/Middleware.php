<?php
namespace rjapi\blocks;

use rjapi\extension\BaseFormRequest;
use rjapi\extension\JSONApiInterface;
use rjapi\helpers\Console;
use rjapi\helpers\MethodOptions;
use rjapi\RJApiGenerator;
use rjapi\helpers\Classes;
use rjapi\types\DefaultInterface;
use rjapi\types\DirsInterface;
use rjapi\types\MethodsInterface;
use rjapi\types\MiddlewareInterface;
use rjapi\types\PhpEntitiesInterface;
use rjapi\types\RamlInterface;

class Middleware extends FormRequestModel
{
    use ContentManager;

    protected $sourceCode = '';
    /** @var RJApiGenerator generator */
    protected $generator       = null;
    private   $additionalProps = [
        'id' => [
            'type' => 'integer',
        ],
    ];
    private   $className       = '';

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
        $fileForm  = $this->generator->formatMiddlewarePath()
                     . PhpEntitiesInterface::SLASH
                     . $this->className
                     . DefaultInterface::MIDDLEWARE_POSTFIX
                     . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile(
            $fileForm, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($fileForm . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setProps($relationTypes = null)
    {
        // additional props
        if(!empty($this->additionalProps))
        {
            foreach($this->additionalProps as $prop => $propVal)
            {
                $this->createProperty($prop, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
            }
        }

        // properties creation
        $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::COMMENT . ' Attributes' . PHP_EOL;
        foreach($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_ATTRS]]
        [RamlInterface::RAML_PROPS] as $propKey => $propVal)
        {
            if(is_array($propVal))
            {
                $this->createProperty($propKey, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
            }
        }
        $this->sourceCode .= PHP_EOL;

        // related props
        if($relationTypes !== null)
        {
            $this->sourceCode .= PhpEntitiesInterface::TAB_PSR4 . PhpEntitiesInterface::COMMENT . ' Relations' .
                                 PHP_EOL;
            foreach($relationTypes as $attrKey => $attrVal)
            {
                // determine attr
                if($attrKey !== RamlInterface::RAML_ID && $attrKey !== RamlInterface::RAML_TYPE)
                {
                    $this->createProperty($attrKey, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
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
        $methodOptions->setName(PhpEntitiesInterface::PHP_AUTHORIZE);
        $methodOptions->setReturnType(PhpEntitiesInterface::PHP_TYPES_BOOL);
        $this->startMethod($methodOptions);
        $this->setMethodReturn(PhpEntitiesInterface::PHP_TYPES_BOOL_TRUE);
        $this->endMethod();

        // Rules method
        $methodOptions->setName(PhpEntitiesInterface::PHP_RULES);
        $methodOptions->setReturnType(PhpEntitiesInterface::PHP_TYPES_ARRAY);
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
        $methodOptions->setReturnType(PhpEntitiesInterface::PHP_TYPES_ARRAY);
        $this->startMethod($methodOptions);
        // attrs validation
        $this->startArray();
        $rel = empty($relationTypes[RJApiGenerator::RAML_TYPE]) ? $relationTypes :
            $relationTypes[RJApiGenerator::RAML_TYPE];

        $rels = explode(PhpEntitiesInterface::PIPE, str_replace('[]', '', $rel));
        foreach($rels as $k => $rel)
        {
            $this->setRelations(strtolower(trim(str_replace(RJApiGenerator::CUSTOM_TYPES_RELATIONSHIPS, '', $rel))));
            if(empty($rels[$k + 1]) === false)
            {
                $this->sourceCode .= PHP_EOL;
            }
        }
        $this->endArray();
        $this->endMethod();
    }

    private function setRelations($relationTypes)
    {
        $this->setTabs(3);
        $this->sourceCode .= PhpEntitiesInterface::DOUBLE_QUOTES . $relationTypes .
                             PhpEntitiesInterface::DOUBLE_QUOTES
                             . PhpEntitiesInterface::COMMA;
    }

    /**
     *  Sets content of *Middleware
     */
    private function setContent()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->httpDir .
            PhpEntitiesInterface::BACKSLASH .
            $this->generator->middlewareDir
        );

        $baseFullForm = BaseFormRequest::class;
        $baseFormName = Classes::getName($baseFullForm);
        $this->setUse($baseFullForm, false, true);
        $this->startClass($this->className . DefaultInterface::MIDDLEWARE_POSTFIX, $baseFormName);

        if(!empty($this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE])
            &&
            !empty($this->generator->types[$this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]])
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
        if(!empty($this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]))
        {
            $this->constructRelations($this->generator->objectProps[RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]);
        }
        // create closing brace
        $this->endClass();
    }

    public function createAccessToken()
    {
        $this->setAccessTokenContent();
        $fileForm  = strtolower(DirsInterface::APPLICATION_DIR)
            . PhpEntitiesInterface::SLASH
            . JSONApiInterface::CLASS_API_ACCESS_TOKEN
            . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile(
            $fileForm, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($fileForm . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }        
    }

    private function setAccessTokenContent()
    {
        $this->setTag();
        $this->sourceCode .= PhpEntitiesInterface::PHP_NAMESPACE . PhpEntitiesInterface::SPACE .
            DirsInterface::APPLICATION_DIR . PhpEntitiesInterface::BACKSLASH . $this->generator->httpDir .
            PhpEntitiesInterface::BACKSLASH . $this->generator->middlewareDir
            . PhpEntitiesInterface::SEMICOLON . PHP_EOL . PHP_EOL;

        $baseFullForm = BaseFormRequest::class;
        $baseFormName = Classes::getName($baseFullForm);
        $this->setUse($baseFullForm, false, true);
        $this->startClass($this->className . DefaultInterface::MIDDLEWARE_POSTFIX, $baseFormName);
        $methodOptions = new MethodOptions();
        $methodOptions->setName(MiddlewareInterface::METHOD_HANDLE);
        $methodOptions->setParams([
            MiddlewareInterface::METHOD_PARAM_REQUEST,
            PhpEntitiesInterface::CLASS_CLOSURE => MiddlewareInterface::METHOD_PARAM_NEXT
        ]);
        
        $this->startMethod($methodOptions);
        $this->endClass();
    }
}