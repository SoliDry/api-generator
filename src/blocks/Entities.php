<?php

namespace rjapi\blocks;

use rjapi\extension\BaseModel;
use rjapi\helpers\Classes;
use rjapi\helpers\Console;
use rjapi\RJApiGenerator;

class Entities extends FormRequestModel
{
    use ContentManager, EntitiesTrait;
    /** @var RJApiGenerator $generator */
    private $generator = null;
    protected $sourceCode = '';
    protected $localCode = '';

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
        $baseMapper = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->generator->objectName, $baseMapperName);

        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC,
            PhpEntitiesInterface::PHP_TYPES_BOOL_FALSE
        );
        $this->setRelations();
        $this->endClass();

        $file = $this->generator->formatEntitiesPath() .
            PhpEntitiesInterface::SLASH .
            $this->generator->objectName . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options));
        if ($isCreated) {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    private function setRelations()
    {
        $middlewareEntity = $this->getMiddleware($this->generator->version, $this->generator->objectName);
        $middleWare = new $middlewareEntity();

        if (method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach ($relations as $relationEntity) {
                $ucEntitty = ucfirst($relationEntity);
                $current = '';
                $related = '';
                // determine if ManyToMany, OneToMany, OneToOne rels
                if (empty($this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                    [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                ) {
                    $current = trim(
                        $this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if (empty($this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                    [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                ) {
                    $related = trim(
                        $this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                    );
                }
                if (empty($current) === false && empty($related) === false) {
                    $this->createRelationMethod($current, $related, $relationEntity);
                }
            }
        }
    }

    /**
     * @param string $current           current entity relations
     * @param string $related           entities from raml file based on relations method array
     * @param string $relationEntity
     */
    private function createRelationMethod(string $current, string $related, string $relationEntity)
    {
        $ucEntitty = ucfirst($relationEntity);
        $currentRels = explode(PhpEntitiesInterface::PIPE, $current);
        $relatedRels = explode(PhpEntitiesInterface::PIPE, $related);
        foreach ($relatedRels as $related) {
            if (strpos($related, $this->generator->objectName) !== false) {
                foreach ($currentRels as $current) {
                    if (strpos($current, $ucEntitty) !== false) {
                        $isManyCurrent = strpos($current, self::CHECK_MANY_BRACKETS);
                        $isManyRelated = strpos($related, self::CHECK_MANY_BRACKETS);
                        if ($isManyCurrent === false && $isManyRelated === false) {// OneToOne
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_ONE);
                        }
                        if ($isManyCurrent !== false && $isManyRelated === false) {// ManyToOne
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_HAS_MANY);
                        }
                        if ($isManyCurrent === false && $isManyRelated !== false) {// OneToMany inverse
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO);
                        }
                        if ($isManyCurrent !== false && $isManyRelated !== false) {// ManyToMany
                            // check inversion of a pivot
                            $entityFile = $this->generator->formatEntitiesPath()
                                . PhpEntitiesInterface::SLASH . $this->generator->objectName . ucfirst($relationEntity) .
                                PhpEntitiesInterface::PHP_EXT;
                            $relEntity = $relationEntity;
                            $objName = $this->generator->objectName;
                            if (file_exists($entityFile) === false) {
                                $relEntity = $this->generator->objectName;
                                $objName = $relationEntity;
                            }
                            $this->setRelation($relationEntity, ModelsInterface::MODEL_METHOD_BELONGS_TO_MANY,
                                strtolower($objName . PhpEntitiesInterface::UNDERSCORE . $relEntity));
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
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->generator->objectName . $ucEntity, $baseMapperName);

        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            RamlInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpEntitiesInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName . PhpEntitiesInterface::UNDERSCORE . $ucEntity), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC,
            PhpEntitiesInterface::PHP_TYPES_BOOL_TRUE
        );

        $this->endClass();

        $file = $this->generator->formatEntitiesPath() .
            PhpEntitiesInterface::SLASH .
            $this->generator->objectName . $ucEntity . PhpEntitiesInterface::PHP_EXT;
        $isCreated = FileManager::createFile($file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options));
        if ($isCreated) {
            Console::out($file . PhpEntitiesInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    public function createPivot()
    {
        $middlewareEntity = $this->getMiddleware($this->generator->version, $this->generator->objectName);
        $middleWare = new $middlewareEntity();

        if (method_exists($middleWare, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $relations = $middleWare->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach ($relations as $relationEntity) {
                $ucEntitty = ucfirst($relationEntity);
                $file = $this->generator->formatEntitiesPath()
                    . PhpEntitiesInterface::SLASH . ucfirst($relationEntity) . $this->generator->objectName .
                    PhpEntitiesInterface::PHP_EXT;
                // check if inverse Entity pivot exists
                if (file_exists($file) === false) {
                    $current = '';
                    $related = '';
                    // determine if ManyToMany, OneToMany, OneToOne rels
                    if (empty($this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                    ) {
                        $current = trim(
                            $this->generator->types[$this->generator->objectName][RamlInterface::RAML_PROPS]
                            [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                        );
                    }
                    if (empty($this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                        [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]) === false
                    ) {
                        $related = trim(
                            $this->generator->types[$ucEntitty][RamlInterface::RAML_PROPS]
                            [RamlInterface::RAML_RELATIONSHIPS][RamlInterface::RAML_TYPE]
                        );
                    }
                    if (empty($current) === false && empty($related) === false) {
                        $this->createPivotClass($current, $related, $relationEntity);
                    }
                }
            }
        }
    }

    /**
     * @param string $current           current entity relations
     * @param string $related           entities from raml file based on relations method array
     * @param string $relationEntity
     */
    private function createPivotClass(string $current, string $related, string $relationEntity)
    {
        $ucEntitty = ucfirst($relationEntity);
        $currentRels = explode(PhpEntitiesInterface::PIPE, $current);
        $relatedRels = explode(PhpEntitiesInterface::PIPE, $related);
        foreach ($relatedRels as $related) {
            if (strpos($related, $this->generator->objectName) !== false) {
                foreach ($currentRels as $current) {
                    if (strpos($current, $ucEntitty) !== false) {
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

    /**
     * @param string $entity
     * @param string $method
     * @param \string[] ...$args
     */
    private function setRelation(string $entity, string $method, string ...$args)
    {
        $this->startMethod($entity, PhpEntitiesInterface::PHP_MODIFIER_PUBLIC);
        $toReturn = PhpEntitiesInterface::DOLLAR_SIGN . PhpEntitiesInterface::PHP_THIS
            . PhpEntitiesInterface::ARROW . $method
            . PhpEntitiesInterface::OPEN_PARENTHESES . ucfirst($entity)
            . PhpEntitiesInterface::DOUBLE_COLON . PhpEntitiesInterface::PHP_CLASS;

        if (empty($args) === false) {
            foreach ($args as $val) {
                $toReturn .= PhpEntitiesInterface::COMMA
                    . PhpEntitiesInterface::SPACE . PhpEntitiesInterface::QUOTES . $val . PhpEntitiesInterface::QUOTES;
            }
        }
        $toReturn .= PhpEntitiesInterface::CLOSE_PARENTHESES;
        $this->methodReturn($toReturn);
        $this->endMethod();
    }
}