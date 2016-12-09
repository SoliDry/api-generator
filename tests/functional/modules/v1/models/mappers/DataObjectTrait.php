<?php
namespace app\modules\v1\models\mappers;

use rjapi\extension\json\api\db\BaseMapperQuery;
use yii\base\ErrorException;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

//TODO: Применить статическое кешировани для геттеров/сеттров
trait DataObjectTrait
{
    private $attributeLinks     = null;
    private $attributeGetters   = [];
    private $objectContainers   = [];
    private $objectQueries      = [];
    private $attributeSetters   = [];
    private $attributeUnsetters = [];
    
    public function __get($name)
    {
        if ($this->hasAttributeContainer($name) === true) {
            return $this->getAttribute($name);
        }
        
        return parent::__get($name);
    }
    
    public function __set($name, $value)
    {
        if ($this->hasAttributeContainer($name) === true) {
            return $this->setAttribute($name, $value);
        }
        
        return parent::__set($name, $value);
    }
    
    private function hasAttributeContainer(string $name) : bool
    {
        return empty($this->getAttributeLinks()[$name]) === false;
    }
    
    protected function getAttributeLinks() : array
    {
        if (is_null($this->attributeLinks) === true) {
            $this->attributeLinks = [];
            
            foreach ($this->containers() as $containerName => $containerConfig) {
                if (isset($containerConfig['attributes']) === true) {
                    foreach ($containerConfig['attributes'] as $index => $name) {
                        if (is_numeric($index)) {
                            $attributeName = $name;
                            $attributeLink = [$containerName, $name];
                        } elseif (is_string($index) === true && is_string($name) === true) {
                            $attributeName = $index;
                            $attributeLink = [$containerName, $name];
                        } elseif (is_string($index) === true && is_array($name) === true) {
                            $attributeName = $index;
                            $attributeLink = $name;
                        } else {      //TODO: Создать возможность конфигурирования геттеров и сеттеров
                            throw new InvalidConfigException(sprintf(
                                'The "%s" attribute is not correctly configured! '
                                . 'See configuration container in "%s::getContainers()".',
                                $index,
                                get_class($this)
                            ));
                        }
                        
                        $this->attributeLinks[$attributeName] = $attributeLink;
                    }
                }
            }
        }
        
        return $this->attributeLinks;
    }
    
    public function getAttribute($name)
    {
        if ($this->hasAttributeContainer($name) === true) {
            return call_user_func($this->resolveAttributeGetter($name));
        }
        
        return parent::getAttribute($name);
    }
    
    private function resolveAttributeGetter($name)
    {
        if (isset($this->attributeGetters[$name]) === false) {
            $links = $this->getAttributeLinks();
            
            if (isset($links[$name]) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not declared! See configuration container in "%s::getContainers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $getter = $links[$name];
            
            //Если это строка то считаем что это имя контейнера
            if (is_string($getter) === true) {
                $getter = $this->formatGetterContainerAttribute($getter, $name);
            } elseif (is_array($getter) === true) { // Если массив то считаем что первый элемент массива это имя
                // контенера а второй это имя атрибута конейнера
                $getter = $this->formatGetterContainerAttribute($getter[0], $getter[1]);
            }
            
            if (is_callable($getter) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not correctly configured! '
                    . 'See configuration container in "%s::getContainers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $this->attributeGetters[$name] = $getter;
        }
        
        return $this->attributeGetters[$name];
    }
    
    private function formatGetterContainerAttribute(string $containerName, string $attributeName)
    {
        return function () use ($containerName, $attributeName) {
            return $this->getContainer($containerName)->{$attributeName};
        };
    }
    
    public function getContainer(string $name, bool $create = false) : ActiveRecord
    {
        if (isset($this->objectContainers[$name]) === false) {
            $query     = $this->getContainerQuery($name);
            $container = null;
            
            //TODO: Продумать работу с множественными связями
            if (true === $query->multiple) {
                throw new ErrorException('Multiplicity is not supported');
            } elseif (false === $create) {
                $container = $query->one();
            }
            
            if (is_null($container) === true) {
                /** @var ActiveRecord $class */
                $class     = $this->getContainerClass($name);
                $container = new $class();
            }
            
            $this->objectContainers[$name] = $container;
        }
        
        return $this->objectContainers[$name];
    }
    
    private function getContainerQuery(string $name) : ActiveQuery
    {
        if (isset($this->objectQueries[$name]) === false) {
            $configuration = $this->getContainerConfiguration($name);
            
            $query = null;
            //TODO: проверки массива
            if ($configuration['pluralize'] === true) {
                /** @var BaseMapperQuery $query */
                $query = $this->hasMany($configuration['class'], $configuration['link']);
                
                if (is_array($configuration['via']) === true) {
                    $query->viaTable($configuration['via']['table'], $configuration['via']['link']);
                }
            } else {
                $query = $this->hasOne($configuration['class'], $configuration['link']);
            }
            
            $this->objectQueries[$name] = $query;
        }
        
        return $this->objectQueries[$name];
    }
    
    private function getContainerConfiguration(string $name) : array
    {
        $configurations = $this->containers();
        
        if (isset($configurations[$name]) === false) {
            throw new InvalidConfigException(sprintf(
                'The "%s" container is not declared! See configuration container in "%s::getContainers()".',
                $name,
                get_class($this)
            ));
        }
        
        return array_merge($this->defaultContainerConfiguration(), $configurations[$name]);
    }
    
    protected function defaultContainerConfiguration()
    {
        return [
            'pluralize' => false,
            'via'       => false,
        ];
    }
    
    private function getContainerClass(string $name) : string
    {
        $configuration = $this->getContainerConfiguration($name);
        
        if (isset($configuration['class']) === false) {
            throw new InvalidConfigException(sprintf(
                'Container "%s" configuration must be an array containing a "class" element.',
                $name
            ));
        }
        
        return $configuration['class'];
    }
    
    public function setAttribute($name, $value)
    {
        if ($this->hasAttributeContainer($name) === true) {
            return call_user_func($this->resolveAttributeSetter($name), $value);
        }
        
        return parent::setAttribute($name, $value);
    }
    
    private function resolveAttributeSetter($name)
    {
        if (isset($this->attributeSetters[$name]) === false) {
            $links = $this->getAttributeLinks();
            
            if (isset($links[$name]) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not declared! See configuration container in "%s::containers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $setter = $links[$name];
            
            //Если это строка то считаем что это имя контейнера
            if (is_string($setter) === true) {
                $setter = $this->formatSetterContainerAttribute($setter, $name);
            } elseif (is_array($setter) === true) { // Если массив то считаем что первый элемент массива это имя
                // контенера а второй это имя атрибута конейнера
                $setter = $this->formatSetterContainerAttribute($setter[0], $setter[1]);
            }
            
            if (is_callable($setter) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not correctly configured! '
                    . 'See configuration container in "%s::containers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $this->attributeSetters[$name] = $setter;
        }
        
        return $this->attributeSetters[$name];
    }
    
    private function formatSetterContainerAttribute(string $containerName, string $attributeName)
    {
        return function ($value) use ($containerName, $attributeName) {
            $this->getContainer($containerName)->{$attributeName} = $value;
        };
    }
    
    abstract public function containers() : array;
    
    public function __unset($name)
    {
        if ($this->hasAttribute($name)) {
            $this->unsetAttribute($name);
        } else {
            parent::__unset($name);
        }
    }
    
    public function hasAttribute($name)
    {
        return in_array($name, $this->attributes()) === true;
    }
    
    public function attributes()
    {
        return array_merge(parent::attributes(), array_keys($this->getAttributeLinks()));
    }
    
    public function unsetAttribute($name)
    {
        $closure = $this->resolveAttributeUnsetter($name);
        
        call_user_func($closure);
    }
    
    private function resolveAttributeUnsetter($name)
    {
        if (isset($this->attributeUnsetters[$name]) === false) {
            $links = $this->getAttributeLinks();
            
            if (isset($links[$name]) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not declared! See configuration container in "%s::containers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $unsetter = $links[$name];
            
            //Если это строка то считаем что это имя контейнера
            if (is_string($unsetter) === true) {
                $unsetter = $this->formatSetterContainerAttribute($unsetter, $name);
            }
            
            if (is_callable($unsetter) === false) {
                throw new InvalidConfigException(sprintf(
                    'The "%s" attribute is not correctly configured! '
                    . 'See configuration container in "%s::containers()".',
                    $name,
                    get_class($this)
                ));
            }
            
            $this->attributeUnsetters[$name] = $unsetter;
        }
        
        return $this->attributeUnsetters[$name];
    }
    
    public function beforeSave($insert)
    {
        /** @var ActiveRecord $this */
        $result      = true;
        $transaction = $this->getDb()->beginTransaction();
        try {
            foreach ($this->getContainers() as $containerName => $container) {
                if (is_array($container) === false) {
                    /** @var ActiveRecord $container */
                    $result = $container->save() === true && $result === true;
                    
                    if (false === $result) {
                        throw new ErrorException(sprintf(
                            'The object "name" could not save the container "name"',
                            get_class($this),
                            get_class($container)
                        ));
                    }
                    
                    foreach ($this->getContainerLinks($containerName) as $containerField => $selfField) {
                        $this->{$selfField} = $container->{$containerField};
                    }
                } else {
                    //TODO: Продумать интрумент линкования многие ко многим
                    throw new ErrorException('Multiplicity is not supported');
                }
            }
            
            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }
        
        return $result === true && parent::beforeSave($insert) === true;
    }
    
    private function getContainers() : array
    {
        return $this->objectContainers;
    }
    
    private function getContainerLinks(string $name) : array
    {
        $configuration = $this->getContainerConfiguration($name);
        
        if (isset($configuration['link']) === false) {
            throw new InvalidConfigException(sprintf(
                'The "%s" object container "%s" configuration must be an array containing a "links" element.',
                get_class($this),
                $name
            ));
        }
        
        if (is_array($configuration['link']) === false) {
            throw new InvalidConfigException(sprintf(
                'The "%s" object container "%s" configuration "links" element must be an array.',
                get_class($this),
                $name
            ));
        }
        
        return $configuration['link'];
    }
    
    public function beforeValidate()
    {
        $result = true;
        
        foreach (array_keys($this->containers()) as $containerName) {
            $container = $this->getContainer($containerName);
            $result    = $container->validate() === true && $result === true;
        }
        
        return $result === true && parent::beforeValidate() === true;
    }
}