<?php

namespace SoliDry\Transformers;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;
use SoliDry\Blocks\EntitiesTrait;
use SoliDry\Exceptions\ModelException;
use SoliDry\Extension\BaseFormRequest;
use SoliDry\Extension\BaseModel;
use SoliDry\Helpers\ConfigHelper;
use SoliDry\Helpers\MigrationsHelper;

/**
 * Class DefaultTransformer
 * transforms data into json-api reliable format for any abstract entities
 *
 * @package SoliDry\Transformers
 */
class DefaultTransformer extends TransformerAbstract
{
    use EntitiesTrait;

    public const INCLUDE_PREFIX = 'include';

    private $formRequest;

    /**
     * DefaultTransformer constructor.
     *
     * @param BaseFormRequest $formRequest
     */
    public function __construct(BaseFormRequest $formRequest)
    {
        $this->formRequest = $formRequest;
        $this->setAvailableIncludes($formRequest->relations());
    }

    /**
     * @param BaseModel | Collection $object
     *
     * @return array
     */
    public function transform($object)
    {
        $arr = [];
        if ($object instanceof BaseModel) {
            $props = get_object_vars($this->formRequest);
            try {
                foreach ($props as $prop => $value) {
                    $arr[$prop] = $object->$prop;
                }
            } catch (ModelException $e) {
                $e->getTraceAsString();
            }
        }
        if ($object instanceof Collection) {
            foreach ($object as $k => $v) {
                $attrs = $v->getAttributes();
                if (empty($attrs) === false) {
                    return $attrs;
                }
            }
        }

        return $arr;
    }

    /**
     * @param string $name     Method name
     * @param array $arguments Method arguments
     *
     * @return \League\Fractal\Resource\Collection | \League\Fractal\Resource\Item
     */
    public function __call($name, $arguments)
    {
        // getting entity relation name, ex.: includeAuthor - author
        $entityName = str_replace(self::INCLUDE_PREFIX, '', $name);
        $formRequestEntity = $this->getFormRequestEntity(ConfigHelper::getModuleName(), $entityName);
        $formRequest = new $formRequestEntity();
        $entityNameLow = MigrationsHelper::getTableName($entityName);
        // getting object, ex.: Book
        $obj = $arguments[0];
        $entity = $obj->$entityNameLow;
        if ($entity instanceof Collection) {
            return $this->collection($entity, new DefaultTransformer($formRequest), $entityNameLow);
        }
        return $this->item($entity, new DefaultTransformer($formRequest), $entityNameLow);
    }
}