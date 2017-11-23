<?php

namespace Grapesc\GrapeFluid\MagicControl;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
interface IMagicControl
{

	/**
	 * @param array $params
	 */
	public function setParams(array $params = []);


	/**
	 * @return void
	 */
	public function render();


	/**
	 * @return void
	 */
	public function prepare();


	/**
	 * @return string|null
	 */
	public function getSnippetElement();
	
}
