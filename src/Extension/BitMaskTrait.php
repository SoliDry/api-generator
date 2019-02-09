<?php
namespace SoliDry\Extension;

use Illuminate\Database\Eloquent\Collection;

/**
 * Class BitMaskTrait
 * @package SoliDry\Extension
 * @property BitMask bitMask
 */
trait BitMaskTrait
{
    /**
     * @param Collection $data
     * @return Collection
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setFlagsIndex(Collection $data)
    {
        $data->map(function($v) {
            $field = $this->bitMask->getField();
            if(isset($v[$field])) {
                $flags = $this->bitMask->getFlags();
                $mask  = $v[$field];
                foreach($flags as $flag => $fVal) {
                    $v[$flag] = (bool)($fVal & $mask);
                }
            }
            return $v;
        });
        return $data;
    }

    /**
     * @param BaseModel $data
     * @return BaseModel
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setFlagsView(BaseModel $data)
    {
        $field = $this->bitMask->getField();
        if(isset($data[$field])) {
            $flags = $this->bitMask->getFlags();
            $mask  = $data[$field];
            foreach($flags as $flag => $fVal) {
                $data[$flag] = ($fVal & $mask) ? true : false;
            }
        }
        return $data;
    }

    /**
     * Creates bit mask based on bit flags and unset those flags to save via model
     * @param array $jsonProps
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setMaskCreate(array $jsonProps)
    {
        $field = $this->bitMask->getField();
        $flags = $this->bitMask->getFlags();
        foreach($flags as $flag => $fVal) {
            if (isset($jsonProps[$flag])) {
                if (true === (bool) $jsonProps[$flag]) {
                    $this->model->$field |= $fVal;
                } else if (false === (bool) $jsonProps[$flag]) {
                    $this->model->$field &= ~$fVal;
                }
            }
            unset($this->model->$flag);
        }
    }

    /**
     * Sets flags on model to pass them through json api processing
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setFlagsCreate()
    {
        $field = $this->bitMask->getField();
        if(isset($this->model->$field)) {
            $flags = $this->bitMask->getFlags();
            $mask  = $this->model->$field;
            foreach($flags as $flag => $fVal) {
                $this->model->$flag = (bool)($fVal & $mask);
            }
        }
        return $this->model;
    }

    /**
     * Updates bit mask based on bit flags and unset those flags to save via model
     * @param $model
     * @param array $jsonProps
     * @return mixed
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setMaskUpdate(&$model, array $jsonProps)
    {
        $field = $this->bitMask->getField();
        $flags = $this->bitMask->getFlags();
        foreach($flags as $flag => $fVal) {
            if (isset($jsonProps[$flag])) {
                if (true === (bool) $jsonProps[$flag]) {
                    $model->$field |= $fVal;
                } else if (false === (bool) $jsonProps[$flag]) {
                    $model->$field &= ~$fVal;
                }
            }
        }
        return $model;
    }

    /**
     * Sets flags on model to pass them through json api processing
     * @param $model
     * @throws \SoliDry\Exceptions\AttributesException
     */
    protected function setFlagsUpdate(&$model) : void
    {
        $field = $this->bitMask->getField();
        if(isset($model[$field])) {
            $flags = $this->bitMask->getFlags();
            $mask  = $model[$field];
            foreach($flags as $flag => $fVal) {
                $model[$flag] = (bool)($fVal & $mask);
            }
        }
    }
}