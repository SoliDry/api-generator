<?php

namespace rjapi\blocks;

use rjapi\extension\BaseModel;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\helpers\MethodOptions;
use rjapi\helpers\MigrationsHelper;
use rjapi\RJApiGenerator;
use rjapi\types\ModelsInterface;
use rjapi\types\PhpInterface;
use rjapi\types\RamlInterface;

/**
 * Class Middleware
 * @package rjapi\blocks
 * @property RJApiGenerator generator
 */
class Entities extends FormRequestModel
{
    use ContentManager, EntitiesTrait;
    /** @var RJApiGenerator $generator */
    private $generator = null;
    private $className = '';

    protected $sourceCode = '';
    protected $localCode  = '';

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
        $file      = $this->generator->formatEntitiesPath() . PhpInterface::SLASH . $this->className .
            PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile(
            $file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setRelations()
    {
        $middlewareEntity = $this->getMiddlewareEntity($this->generator->version, $this->className);
        $middleWare       = new $middlewareEntity();
        if(method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach($relations as $relationEntity)
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
                    $this->createRelationMethod($current, $related, $relationEntity);
                }
            }
        }
    }

    /**
     * @param string $current current entity relations
     * @param string $related entities from raml file based on relations method array
     * @param string $relationEntity
     */
    private function createRelationMethod(string $current, string $related, string $relationEntity)
    {
        $ucEntitty   = ucfirst($relationEntity);
        $currentRels = explode(PhpInterface::PIPE, $current);
        $relatedRels = explode(PhpInterface::PIPE, $related);
        foreach($relatedRels as $related)
        {
            if(strpos($related, $this->generator->objectName) !== false)
            {
                foreach($currentRels as $current)
                {
                    if(strpos($current, $ucEntitty) !== false)
                    {
                        $isManyCurrent = strpos($current, self::CHECK_MANY_BRACKETS);
                        $isManyRelated = strpos($related, self::CHECK_MANY_BRACKETS);
                        if($isManyCurrent === false && $isManyRelated === false)
                        {// OneToOne
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_ONE);
                        }
                        if($isManyCurrent !== false && $isManyRelated === false)
                        {// ManyToOne
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_MANY);
                        }
                        if($isManyCurrent === false && $isManyRelated !== false)
                        {// OneToMany inverse
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO);
                        }
                        if($isManyCurrent !== false && $isManyRelated !== false)
                        {// ManyToMany
                            // check inversion of a pivot
                            $entityFile = $this->generator->formatEntitiesPath()
                                . PhpInterface::SLASH . $this->generator->objectName .
                                ucfirst($relationEntity) .
                                PhpInterface::PHP_EXT;
                            $relEntity  = $relationEntity;
                            $objName    = $this->generator->objectName;
                            if(file_exists($entityFile) === false)
                            {
                                $relEntity = $this->generator->objectName;
                                $objName   = $relationEntity;
                            }
                            $this->setRelation(
                                $relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO_MANY,
                                MigrationsHelper::getTableName($objName . ucfirst($relEntity))
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string $ucEntity
     */
    public function setPivot(string $ucEntity)
    {
        $this->setPivotContent($ucEntity);
        $file      = $this->generator->formatEntitiesPath() .
            PhpInterface::SLASH .
            $this->className . Classes::getClassName($ucEntity) . PhpInterface::PHP_EXT;
        $isCreated = FileManager::createFile(
            $file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if($isCreated)
        {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    public function createPivot()
    {
        $middlewareEntity = $this->getMiddlewareEntity($this->generator->version, $this->className);
        $middleWare       = new $middlewareEntity();

        if(method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS))
        {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach($relations as $relationEntity)
            {
                $ucEntitty = ucfirst($relationEntity);
                $file      = $this->generator->formatEntitiesPath()
                    . PhpInterface::SLASH . ucfirst($relationEntity) . $this->generator->objectName .
                    PhpInterface::PHP_EXT;
                // check if inverse Entity pivot exists
                if(file_exists($file) === false)
                {
                    $current = '';
                    $related = '';
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
                        $this->createPivotClass($current, $related, $relationEntity);
                    }
                }
            }
        }
    }

    /**
     * @param string $current current entity relations
     * @param string $related entities from raml file based on relations method array
     * @param string $relationEntity
     */
    private function createPivotClass(string $current, string $related, string $relationEntity)
    {
        $ucEntitty   = ucfirst($relationEntity);
        $currentRels = explode(PhpInterface::PIPE, $current);
        $relatedRels = explode(PhpInterface::PIPE, $related);
        foreach($relatedRels as $related)
        {
            if(strpos($related, $this->generator->objectName) !== false)
            {
                foreach($currentRels as $current)
                {
                    if(strpos($current, $ucEntitty) !== false)
                    {
                        $isManyCurrent = strpos($current, self::CHECK_MANY_BRACKETS);
                        $isManyRelated = strpos($related, self::CHECK_MANY_BRACKETS);
                        if($isManyCurrent !== false && $isManyRelated !== false)
                        {// ManyToMany
                            $this->setPivot($ucEntitty);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param string    $entity
     * @param string    $method
     * @param \string[] ...$args
     */
    private function setRelation(string $entity, string $method, string ...$args)
    {
        $methodOptions = new MethodOptions();
        $methodOptions->setName($entity);
        $this->startMethod($methodOptions);
        $toReturn = $this->getRelationReturn($entity, $method, $args);
        $this->setMethodReturn($toReturn);
        $this->endMethod();
    }

    /**
     * @param string $entity
     * @param string $method
     * @param \string[] ...$args
     * @return string
     */
    private function getRelationReturn(string $entity, string $method, array $args)
    {
        $toReturn = PhpInterface::DOLLAR_SIGN . PhpInterface::PHP_THIS
            . PhpInterface::ARROW . $method
            . PhpInterface::OPEN_PARENTHESES . Classes::getClassName($entity)
            . PhpInterface::DOUBLE_COLON . PhpInterface::PHP_CLASS;

        if(empty($args) === false)
        {
            foreach($args as $val)
            {
                $toReturn .= PhpInterface::COMMA
                    . PhpInterface::SPACE . PhpInterface::QUOTES . $val .
                    PhpInterface::QUOTES;
            }
        }
        $toReturn .= PhpInterface::CLOSE_PARENTHESES;
        return $toReturn;
    }

    /**
     * Sets entity content to $sourceCode
     */
    private function setContent()
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->className, $baseMapperName);

        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_FALSE
        );
        $this->setRelations();
        $this->endClass();
    }

    /**
     *  Sets pivot entity content to $sourceCode
     * @param string $ucEntity  an entity upper case first name
     */
    private function setPivotContent(string $ucEntity)
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->className . Classes::getClassName($ucEntity), $baseMapperName);

        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName . PhpInterface::UNDERSCORE . $ucEntity), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_TRUE
        );
        $this->endClass();
    }
}