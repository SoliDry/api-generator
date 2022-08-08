<?php

namespace SoliDry\Blocks;

use Illuminate\Database\Eloquent\SoftDeletes;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\BaseModel;
use SoliDry\Helpers\Classes;
use SoliDry\Helpers\Console;
use SoliDry\Helpers\MethodOptions;
use SoliDry\Helpers\MigrationsHelper;
use SoliDry\ApiGenerator;
use SoliDry\Types\CustomsInterface;
use SoliDry\Types\DefaultInterface;
use SoliDry\Types\ModelsInterface;
use SoliDry\Types\PhpInterface;
use SoliDry\Types\ApiInterface;

/**
 * Class FormRequest
 * @package SoliDry\Blocks
 * @property ApiGenerator generator
 */
class Entities extends FormRequestModel
{
    use ContentManager, EntitiesTrait;

    /**
     * @var ApiGenerator
     */
    private ApiGenerator $generator;

    /**
     * @var string
     */
    private string $className;

    /**
     * @var string
     */
    protected string $sourceCode   = '';

    /**
     * @var bool
     */
    protected bool $isSoftDelete = false;

    /**
     * Entities constructor.
     * @param $generator
     */
    public function __construct($generator)
    {
        $this->generator = $generator;
        $this->className = Classes::getClassName($this->generator->objectName);
        $isSoftDelete    = empty($this->generator->types[$this->generator->objectName . CustomsInterface::CUSTOM_TYPES_ATTRIBUTES]
                                 [ApiInterface::RAML_PROPS][ModelsInterface::COLUMN_DEL_AT]) === false;
        $this->setIsSoftDelete($isSoftDelete);
    }

    public function setCodeState($generator)
    {
        $this->generator = $generator;
    }

    /**
     * @return bool
     */
    public function isSoftDelete() : bool
    {
        return $this->isSoftDelete;
    }

    /**
     * @param bool $isSoftDelete
     */
    public function setIsSoftDelete(bool $isSoftDelete) : void
    {
        $this->isSoftDelete = $isSoftDelete;
    }

    private function setRelations()
    {
        $formRequestEntity = $this->getFormRequestEntity($this->generator->version, $this->className);
        /** @var BaseFormRequest $formRequest * */
        $formRequest = new $formRequestEntity();
        if (method_exists($formRequest, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $this->sourceCode .= PHP_EOL;
            $relations        = $formRequest->relations();
            foreach ($relations as $relationEntity) {
                $ucEntity = MigrationsHelper::getObjectName($relationEntity);
                // determine if ManyToMany, OneToMany, OneToOne rels
                $current = $this->getRelationType($this->generator->objectName);
                $related = $this->getRelationType($ucEntity);
                if (empty($current) === false && empty($related) === false) {
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
        $ucEntity    = ucfirst($relationEntity);
        $currentRels = explode(PhpInterface::PIPE, $current);
        $relatedRels = explode(PhpInterface::PIPE, $related);
        foreach ($relatedRels as $rel) {
            if (strpos($rel, $this->generator->objectName) !== false) {
                foreach ($currentRels as $cur) {
                    if (strpos($cur, $ucEntity) !== false) {
                        $isManyCurrent = strpos($cur, self::CHECK_MANY_BRACKETS);
                        $isManyRelated = strpos($rel, self::CHECK_MANY_BRACKETS);
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
                                . PhpInterface::SLASH . $this->generator->objectName .
                                ucfirst($relationEntity) .
                                PhpInterface::PHP_EXT;
                            $relEntity  = $relationEntity;
                            $objName    = $this->generator->objectName;
                            if (file_exists($entityFile) === false) {
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
     * @throws \ReflectionException
     */
    public function setPivot(string $ucEntity): void
    {
        $file = $this->generator->formatEntitiesPath() .
            PhpInterface::SLASH .
            $this->className . Classes::getClassName($ucEntity) . PhpInterface::PHP_EXT;
        if ($this->generator->isMerge === true) {
            $this->resetPivotContent($ucEntity, $file);
        } else {
            $this->setPivotContent($ucEntity);
        }
        $isCreated = FileManager::createFile(
            $file, $this->sourceCode,
            FileManager::isRegenerated($this->generator->options)
        );
        if ($isCreated) {
            Console::out($file . PhpInterface::SPACE . Console::CREATED, Console::COLOR_GREEN);
        }
    }

    public function createPivot()
    {
        $formRequestEntity = $this->getFormRequestEntity($this->generator->version, $this->className);
        /** @var BaseFormRequest $formRequest * */
        $formRequest = new $formRequestEntity();
        if (method_exists($formRequest, ModelsInterface::MODEL_METHOD_RELATIONS)) {
            $relations        = $formRequest->relations();
            $this->sourceCode .= PHP_EOL; // margin top from props
            foreach ($relations as $relationEntity) {
                $ucEntity = ucfirst($relationEntity);
                $file     = $this->generator->formatEntitiesPath()
                    . PhpInterface::SLASH . ucfirst($relationEntity) . $this->generator->objectName .
                    PhpInterface::PHP_EXT;
                // check if inverse Entity pivot exists
                if (file_exists($file) === false) {
                    // determine if ManyToMany, OneToMany, OneToOne rels
                    $current = $this->getRelationType($this->generator->objectName);
                    $related = $this->getRelationType($ucEntity);
                    if (empty($current) === false && empty($related) === false) {
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
     * @throws \ReflectionException
     */
    private function createPivotClass(string $current, string $related, string $relationEntity): void
    {
        $ucEntity    = ucfirst($relationEntity);
        $currentRels = explode(PhpInterface::PIPE, $current);
        $relatedRels = explode(PhpInterface::PIPE, $related);
        foreach ($relatedRels as $rel) {
            if (strpos($rel, $this->generator->objectName) !== false) {
                foreach ($currentRels as $cur) {
                    if (strpos($cur, $ucEntity) !== false) {
                        $isManyCurrent = strpos($cur, self::CHECK_MANY_BRACKETS);
                        $isManyRelated = strpos($rel, self::CHECK_MANY_BRACKETS);
                        if ($isManyCurrent !== false && $isManyRelated !== false) {// ManyToMany
                            $this->setPivot($ucEntity);
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
    private function setRelation(string $entity, string $method, string ...$args): void
    {
        $methodOptions = new MethodOptions();
        $methodOptions->setName($entity);
        $this->startMethod($methodOptions);
        $toReturn = $this->getRelationReturn($entity, $method, $args);
        $this->setMethodReturn($toReturn);
        $this->endMethod(1);
    }

    /**
     * @param string $entity
     * @param string $method
     * @param \string[] ...$args
     * @return string
     */
    private function getRelationReturn(string $entity, string $method, array $args): string
    {
        $toReturn = PhpInterface::DOLLAR_SIGN . PhpInterface::PHP_THIS
            . PhpInterface::ARROW . $method
            . PhpInterface::OPEN_PARENTHESES . Classes::getClassName($entity)
            . PhpInterface::DOUBLE_COLON . PhpInterface::PHP_CLASS;

        if (empty($args) === false) {
            foreach ($args as $val) {
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
    private function setContent(): void
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        if ($this->isSoftDelete()) {
            $this->setUse(SoftDeletes::class);
        }

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->className, $baseMapperName);
        $this->setUseSoftDelete();
        $this->setComment(DefaultInterface::PROPS_START);
        $this->setPropSoftDelete();
        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            ApiInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            MigrationsHelper::getTableName($this->generator->objectName), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_FALSE
        );
        $this->setIncrementingProperty();
        $this->setComment(DefaultInterface::PROPS_END);
        $this->setComment(DefaultInterface::METHOD_START);
        $this->setRelations();
        $this->setComment(DefaultInterface::METHOD_END);
        $this->endClass();
    }

    private function setIncrementingProperty()
    {
        // O(4)
        $idObject = $this->generator->types[$this->generator->types[$this->generator->objectName][ApiInterface::RAML_PROPS][ApiInterface::RAML_ID]];
        if ($idObject[ApiInterface::RAML_TYPE] === ApiInterface::RAML_TYPE_STRING) {
            $this->createProperty(
                ModelsInterface::PROPERTY_INCREMENTING, PhpInterface::PHP_MODIFIER_PUBLIC,
                PhpInterface::PHP_TYPES_BOOL_FALSE
            );
        }
    }

    /**
     * Sets entity content to $sourceCode
     */
    private function resetContent(): void
    {
        $this->setBeforeProps($this->getEntityFile($this->generator->formatEntitiesPath()));
        $this->setComment(DefaultInterface::PROPS_START, 0);
        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            ApiInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_FALSE
        );
        $this->setAfterProps(DefaultInterface::METHOD_START);
        $this->setComment(DefaultInterface::METHOD_START, 0);
        $this->setRelations();
        $this->setAfterMethods();
    }

    /**
     *  Sets pivot entity content to $sourceCode
     * @param string $ucEntity an entity upper case first name
     * @throws \ReflectionException
     */
    private function setPivotContent(string $ucEntity): void
    {
        $this->setTag();
        $this->setNamespace(
            $this->generator->entitiesDir
        );
        $baseMapper     = BaseModel::class;
        $baseMapperName = Classes::getName($baseMapper);

        $this->setUse($baseMapper, false, true);
        $this->startClass($this->className . Classes::getClassName($ucEntity), $baseMapperName);
        $this->setComment(DefaultInterface::PROPS_START);
        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            ApiInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName . PhpInterface::UNDERSCORE . $ucEntity), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_TRUE
        );
        $this->setComment(DefaultInterface::PROPS_END);
        $this->endClass();
    }

    /**
     *  Re-Sets pivot entity content to $sourceCode
     * @param string $ucEntity an entity upper case first name
     * @param string $file
     */
    private function resetPivotContent(string $ucEntity, string $file)
    {
        $this->setBeforeProps($file);
        $this->setComment(DefaultInterface::PROPS_START, 0);
        $this->createProperty(
            ModelsInterface::PROPERTY_PRIMARY_KEY, PhpInterface::PHP_MODIFIER_PROTECTED,
            ApiInterface::RAML_ID, true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TABLE, PhpInterface::PHP_MODIFIER_PROTECTED,
            strtolower($this->generator->objectName . PhpInterface::UNDERSCORE . $ucEntity), true
        );
        $this->createProperty(
            ModelsInterface::PROPERTY_TIMESTAMPS, PhpInterface::PHP_MODIFIER_PUBLIC,
            PhpInterface::PHP_TYPES_BOOL_TRUE
        );
        $this->setAfterProps();
    }
}