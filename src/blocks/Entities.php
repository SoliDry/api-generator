<?php

namespace rjapi\blocks;

use rjapi\extension\BaseModel;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Entities extends FormRequestModel
{
    use ContentManager;
    /** @var RJApiGenerator $generator */
    private   $generator  = null;
    protected $sourceCode = '';
    protected $localCode  = '';

    public function __construct($generator)
    {
        $this->generator = $generator;
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    public function create()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->generator->objectName, $baseMapperName);

        $this->createProperty(
            DefaultInterface::PRIMARY_KEY_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            DefaultInterface::TABLE_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName), true
        );
        $this->createProperty(
            DefaultInterface::TIMESTAMPS_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC,
            PhpEntitiesInterface::PHP_TYPES_BOOL_FALSE
        );
        $this->setRelations();
        $this->endClass();

        $file      = $this->generator->formatEntitiesPath() .
                     PhpEntitiesInterface::SLASH .
                     $this->generator->objectName . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if($isCreated)
        {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setRelations()
    {
        $middlewareEntity =
            DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . strtoupper($this->generator->version) .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->generator->objectName .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleWare       = new $middlewareEntity();

        if(method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach($relations as $k => $relationEntity)
            {
                $ucEntitty = ucfirst($relationEntity);
                $current   = '';
                $related   = '';
                // determine if ManyToMany, OneToMany, OneToOne rels
                if(empty($this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                         [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                )
                {
                    $current = trim(
                        $this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if(empty($this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                         [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                )
                {
                    $related = trim(
                        $this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if(empty($current) === false && empty($related) === false)
                {
                    // TODO: Process Many Rels with explode like - type: TagRelationships[] | ArticleRelationships[]
                    $isManyCurrent = strpos($current, self::CHECK_MANY_BRACKETS);
                    $isManyRelated = strpos($related, self::CHECK_MANY_BRACKETS);
                    if($isManyCurrent === false && $isManyRelated === false)
                    {// OneToOne
                        $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_ONE);
                    }
                    if($isManyCurrent !== false && $isManyRelated === false)
                    {// ManyToOne
                        $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO);
                    }
                    if($isManyCurrent === false && $isManyRelated !== false)
                    {// OneToMany
                        $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_MANY);
                    }
                    if($isManyCurrent !== false && $isManyRelated !== false)
                    {// ManyToMany
                        $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO_MANY);
                    }
                }
            }
        }
    }

    public function setPivot($ucEntity)
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->generator->objectName . $ucEntity, $baseMapperName);

        $this->createProperty(
            DefaultInterface::PRIMARY_KEY_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            DefaultInterface::TABLE_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName . PhpEntitiesInterface::UNDERSCORE . $ucEntity), true
        );
        // TODO: change hard-code str
        $this->createProperty(
            DefaultInterface::TIMESTAMPS_PROPERTY, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC,
            PhpEntitiesInterface::PHP_TYPES_BOOL_TRUE
        );

        $this->endClass();

        $file      = $this->generator->formatEntitiesPath() .
                     PhpEntitiesInterface::SLASH .
                     $this->generator->objectName . $ucEntity . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode);
        if($isCreated)
        {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    public function createPivot()
    {
        $middlewareEntity =
            DirsInterface::MODULES_DIR . PhpEntitiesInterface::BACKSLASH . strtoupper($this->generator->version) .
            PhpEntitiesInterface::BACKSLASH . DirsInterface::HTTP_DIR .
            PhpEntitiesInterface::BACKSLASH .
            DirsInterface::MIDDLEWARE_DIR . PhpEntitiesInterface::BACKSLASH .
            $this->generator->objectName .
            DefaultInterface::MIDDLEWARE_POSTFIX;
        $middleWare       = new $middlewareEntity();

        if(method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach($relations as $k => $relationEntity)
            {
                $ucEntitty = ucfirst($relationEntity);
                $current   = '';
                $related   = '';
                // determine if ManyToMany, OneToMany, OneToOne rels
                if(empty($this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                    [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                )
                {
                    $current = trim(
                        $this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if(empty($this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                    [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                )
                {
                    $related = trim(
                        $this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if(empty($current) === false && empty($related) === false)
                {
                    $currentRels = explode(PhpEntitiesInterface::PIPE, $current);
                    $relatedRels = explode(PhpEntitiesInterface::PIPE, $related);
                    foreach ($currentRels as $current) {
                        foreach ($relatedRels as $related) {
                            // TODO: Process Many Rels with explode like - type: TagRelationships[] | ArticleRelationships[]
                            $isManyCurrent = strpos($current, self::CHECK_MANY_BRACKETS);
                            $isManyRelated = strpos($related, self::CHECK_MANY_BRACKETS);
                            if ($isManyCurrent !== false && $isManyRelated !== false) {// ManyToMany
                                $this->setPivot($ucEntitty);
                            }
                        }
                    }
                }
            }
        }
    }

    private function setRelation($entity, $method)
    {
        $this->startMethod($entity, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
        $this->methodReturn(
            PhpEntitiesInterface::DOLLAR_SIGN . PhpEntitiesInterface::PHP_THIS
            . PhpEntitiesInterface::ARROW . $method
            . PhpEntitiesInterface::OPEN_PARENTHESES . ucfirst($entity)
            . PhpEntitiesInterface::DOUBLE_COLON . PhpEntitiesInterface::PHP_CLASS
            . PhpEntitiesInterface::CLOSE_PARENTHESES
        );
        $this->endMethod();
    }
}