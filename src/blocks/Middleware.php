<?php
namespace rjapi\blocks;

use rjapi\extension\BaseFormRequest;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;
use rjapi\helpers\Classes;

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
        $this->startMethod(PhpEntitiesInterface::PHP_AUTHORIZE, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_BOOL);
        $this->methodReturn(PhpEntitiesInterface::PHP_TYPES_BOOL_TRUE);
        $this->endMethod();

        // Rules method
        $this->startMethod(PhpEntitiesInterface::PHP_RULES, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_ARRAY);
        // attrs validation
        $this->startArray();
        // gather types and constraints
        $this->setPropertyFilters();
        $this->endArray();
        $this->endMethod();
    }

    private function constructRelations($relationTypes)
    {
        $this->startMethod(RJApiGenerator::PHP_RELATIONS, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC, PhpEntitiesInterface::PHP_TYPES_ARRAY);
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
        $this->sourceCode .= RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4 . RJApiGenerator::TAB_PSR4
                             . PhpEntitiesInterface::DOUBLE_QUOTES . $relationTypes .
                             PhpEntitiesInterface::DOUBLE_QUOTES
                             . PhpEntitiesInterface::COMMA;
    }
}