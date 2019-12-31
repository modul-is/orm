<?php

namespace ModulIS;

use ModulIS\Reflection\AnnotationProperty;
use Nette\Database\Table\ActiveRow as NActiveRow;


abstract class Entity
{

	/** @var Record */
	protected $record;

	/** @var array */
	private static $reflections = [];


	/** @param  NActiveRow|Record $row */
	public function __construct($row = NULL)
	{
		$this->record = Record::create($row);
	}


	final public function toRecord(): Record
	{
		return $this->record;
	}


	/**
	 * @param  string $name
	 * @param  array $args
	 */
	public function __call($name, $args): void
	{
		// events support
		$ref = static::getReflection();
		if (preg_match('#^on[A-Z]#', $name) && $ref->hasProperty($name)) {
			$prop = $ref->getProperty($name);
			if ($prop->isPublic() && !$prop->isStatic() && (is_array($this->$name) || $this->$name instanceof \Traversable)) {
				foreach ($this->$name as $cb) {
					$cb($args);
				}

				return ;
			}
		}

		$class = get_class($this);
		throw new Exception\MemberAccessException("Call to undefined method $class::$name().");
	}


	/**
	 * @param  string $name
	 */
	public function & __get($name)
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			$value = $prop->getValue($this);
			
			if($prop->getType() == 'json')
			{
				if($value !== null)
				{
					$value = \Nette\Utils\Json::decode($value, \Nette\Utils\Json::FORCE_ARRAY);
				}
			}
			
			return $value;
		}				

		throw new Exception\MemberAccessException("Cannot read an undeclared property {$ref->getName()}::\$$name.");
	}


	/**
	 * @param  string $name
	 * @param  mixed $value
	 */
	public function __set($name, $value): void
	{
		$ref = static::getReflection();
		$prop = $ref->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			
			if($prop->getType() == 'json')
			{
				if(is_array($value))
				{
					$value = \Nette\Utils\Json::encode($value);
				}
			}
			
			$prop->setValue($this, $value);
			
			return ;
		}

		throw new Exception\MemberAccessException("Cannot write to an undeclared property {$ref->getName()}::\$$name.");
	}


	/**
	 * @param  string $name
	 */
	public function __isset($name): bool
	{
		$prop = static::getReflection()->getEntityProperty($name);

		if ($prop instanceof AnnotationProperty) {
			return $prop->getValue($this) !== NULL;
		}

		return FALSE;
	}


	/**
	 * @param  string $name
	 * @throws Exception\NotSupportedException
	 */
	public function __unset($name): void
	{
		throw new Exception\NotSupportedException;
	}


	public static function getReflection(): Reflection\EntityType
	{
		$class = get_called_class();
		if (!isset(self::$reflections[$class])) {
			self::$reflections[$class] = new Reflection\EntityType($class);
		}

		return self::$reflections[$class];
	}
	
		    
    public function getModifiedArray(): array
    {
        return $this->record->getModified();
    }

		
    public function toArray(array $excludedProperties = []): array
    {
        if(!$excludedProperties instanceof \Nette\Utils\ArrayHash && !is_array($excludedProperties))
        {
            throw new \Exception('Excluded properties should be Array or \Nette\Utils\ArrayHash');
        }

        $ref = static::getReflection();
	$values = [];

	foreach ($ref->getEntityProperties() as $name => $property)
        {
            if(array_search($name, $excludedProperties) === FALSE && $name != 'modifiedArray')
            {
                if($property instanceof \YetORM\Reflection\MethodProperty)
                {
                    $value = $this->{'get' . $name}();
                }
                else
                {
                    $value = (!empty($this->$name) || $this->$name === 0) ? $this->$name : NULL;
                }

                if(!($value instanceof \YetORM\EntityCollection || $value instanceof \YetORM\Entity))
                {
                    $values[$name] = $value;
                }
            }
	}

        return $values;
    }
	
	
	/**
     * Fill entity from array or ArrayHash
     *
     * @param array|\Nette\Utils\ArrayHash     
     */
    public function fillFromArray($values): void
    {
        $ref = static::getReflection();

        foreach($ref->getEntityProperties() as $name => $property)
        {
            if(!$property->isReadonly())
            {
				/**
				 * Set NULL for nullable properties without value
				 * Skip if property not set and is not nullable
				 */
				if(!isset($values[$name]) && $property->isNullable() && empty($values[$name]))
				{
					$values[$name] = null;
				}
				elseif(!isset($values[$name]) && !$property->isNullable())
				{
					continue;
				}

				/**
				 * Convert bool to int
				 */
				if($property->getType() == 'int' && is_bool($values[$name]))
				{
					$values[$name] = $values[$name] ? 1 : 0;
				}

				/**
				 * Convert strings to int
				 */
				elseif($property->getType() == 'int' && !empty($values[$name]))
				{
					$values[$name] = intval($values[$name]);
				}

				/**
				 * Convert array to json
				 */
				if($property->getDescription() == 'json' && is_array($values[$name]))
				{
					$this->$name = \Nette\Utils\Json::encode($values[$name]);
				}
				else
				{
					$this->$name = $values[$name];
                    }
                
            }
        }
    }
	
}
