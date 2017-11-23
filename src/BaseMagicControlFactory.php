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
		$ap = new ClassType($this);
		if ($ap->hasAnnotation('className') AND class_exists($class = $ap->getAnnotation('className'))) {
			if ($ap->implementsInterface(IMagicControl::class)) {
				throw new \InvalidArgumentException(sprintf('Magic Control "%s" must implement IMagicControl.', get_class($class)));
			}
			return new $class;
		}
		
		throw new \InvalidArgumentException("Annotation 'className' doesn't exists, please override ::create method.");
	}
	
}
