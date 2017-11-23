<?php

namespace Grapesc\GrapeFluid\MagicControl;


/**
 * @author Jiri Novy <novy@grapesc.cz>
 */
interface IMagicTemplate
{

	/**
	 * @param string $componentName
	 * @param null|string $tid
	 * @return null|\SplFileInfo|string
	 */
	public function getTemplateSource($componentName, $tid = null);


	/**
	 * @param string $componentName
	 * @return null|\SplFileInfo|string
	 */
	public function getDefaultTemplateSource($componentName);

}
