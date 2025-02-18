<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Nette\Reflection\ClassType;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
abstract class BaseMagicControlFactory implements IFactory
{

	/**
	 * @return IMagicControl
	 */
	public function create()
	{
		$reflection = new \ReflectionObject($this);
		$docComment = $reflection->getDocComment();

		$class = '';
		if ($docComment) {
			if (preg_match('/@className\s+(\S+)/', $docComment, $matches)) {
				$class = $matches[1]; //@todo check
			}
		}

		if ($class AND class_exists($class)) {
			if ($reflection->implementsInterface(IMagicControl::class)) {
				throw new \InvalidArgumentException(sprintf('Magic Control "%s" must implement IMagicControl.', get_class($class)));
			}
			return new $class;
		}
		
		throw new \InvalidArgumentException("Annotation 'className' doesn't exists, please override ::create method.");
	}
	
}
