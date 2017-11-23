<?php

namespace Grapesc\GrapeFluid\MagicControl;

/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
interface IFactory
{
	
	/** @return \Nette\ComponentModel\IComponent */
	public function create();
	
}
