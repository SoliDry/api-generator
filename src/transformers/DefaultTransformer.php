<?php
/**
 * Created by PhpStorm.
 * User: arthur
 * Date: 19.12.16
 * Time: 20:14
 */

namespace rjapi\transformers;

use League\Fractal\TransformerAbstract;
use rjapi\exception\ModelException;
use rjapi\extension\BaseFormRequest;
use rjapi\extension\BaseModel;

class DefaultTransformer extends TransformerAbstract
{
    const INCLUDE_PREFIX = 'include';

    private $middleWare = null;

    public function __construct(BaseFormRequest $middleWare)
    {
        $this->middleWare = $middleWare;
        $this->setAvailableIncludes($middleWare->relations());
    }

    public function transform(BaseModel $object)
    {
        $props = get_object_vars($this->middleWare);
        $arr = [];
        try {
            foreach ($props as $prop => $value) {
                $arr[$prop] = $object->$prop;
            }
        } catch (ModelException $e) {
            $e->getTraceAsString();
        }

        return $arr;
    }

    public function __call($name, $arguments)
    {
        // getting entity relation name, ex.: includeAuthor - author
        $entityName = strtolower(str_replace(self::INCLUDE_PREFIX, '', $name));
        // getting object, ex.: Book
        $obj = $arguments[0];
        $entity = $obj->$entityName;
        return $this->item($entity, new DefaultTransformer($this->middleWare), $entityName);
    }
}