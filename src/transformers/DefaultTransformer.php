<?php
namespace rjapi\transformers;

use Illuminate\Database\Eloquent\Collection;
use League\Fractal\TransformerAbstract;
use rjapi\blocks\EntitiesTrait;
use rjapi\exception\ModelException;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;
use rjapi\helpers\ConfigHelper;
use rjapi\helpers\MigrationsHelper;

class DefaultTransformer extends TransformerAbstract
{
    use EntitiesTrait;

    const INCLUDE_PREFIX = 'include';

    private $middleWare = null;

    /**
     * DefaultTransformer constructor.
     *
     * @param BaseFormRequest $middleWare
     */
    public function __construct(BaseFormRequest $middleWare)
    {
        $this->middleWare = $middleWare;
        $this->setAvailableIncludes($middleWare->relations());
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
            $props = get_object_vars($this->middleWare);
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
     * @return \League\Fractal\Resource\Collection
     */
    public function __call($name, $arguments)
    {
        // getting entity relation name, ex.: includeAuthor - author
        $entityName = str_replace(self::INCLUDE_PREFIX, '', $name);
        $middlewareEntity = $this->getMiddlewareEntity(ConfigHelper::getModuleName(), $entityName);
        $middleWare = new $middlewareEntity();
        $entityNameLow = MigrationsHelper::getTableName($entityName);
        // getting object, ex.: Book
        $obj = $arguments[0];
        $entity = $obj->$entityNameLow;
        if ($entity instanceof Collection) {
            return $this->collection($entity, new DefaultTransformer($middleWare), $entityNameLow);
        }
        return $this->item($entity, new DefaultTransformer($middleWare), $entityNameLow);
    }
}