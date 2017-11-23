<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Grapesc\GrapeFluid\ScriptCollector;
use Nette\Application\UI\Control;


/**
 * @author Kulisek Patrik <kulisek@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
abstract class BaseMagicControl extends Control implements IMagicControl
{

	/** @var ScriptCollector @inject */
	public $scriptCollector;

	/** @var Creator @inject */
	public $magicControlCreator;
	
	/** @var string|null */
	protected $snippetElement = 'div';


	/**
	 * Překreslí aktuální komponentu
	 * @param string|null $snippet
	 * @return void
	 */
	public function redrawComponent($snippet = null)
	{
		/** @var MagicControl $magicControl */
		$magicControl = $this->getParent();
		$magicControl->redrawComponent($snippet);
	}


	/**
	 * Spustí se před renderem samotné komponenty
	 *
	 * Slouží k připravení template pro scriptCollector,
	 * který nechá obsah template vykreslit v patičce webu
	 *
	 * @return void
	 */
	public function prepare()
	{
	}


	/**
	 * @return null|string
	 */
	public function getSnippetElement()
	{
		return $this->snippetElement;
	}


	/**
	 * @param string|null $snippet
	 * @param bool $redraw
	 */
	public function redrawControl($snippet = null, $redraw = true)
	{
		/** @var MagicControl $magicControl */
		$magicControl = $this->getParent();
		if (!$magicControl->isRedrawRequired()) {
			$this->redrawComponent($snippet);
		} else {
			parent::redrawControl($snippet, $redraw);
		}
	}


	/**
	 * @param $name
	 * @return MagicControl|\Nette\ComponentModel\IComponent
	 */
	protected function createComponent($name)
	{
		if ($this->getPresenter()->isAjax()) {
			$tempControlParams = $this->magicControlCreator->getControlParams($this->getName());
			if (isset($tempControlParams[0])) {
				$this->setParams($tempControlParams[0]);
			}
		}

		if (substr($name, 0, 3) == 'mc_') {
			$exploded = explode("_", $name);

			unset($exploded[sizeof($exploded) - 1]);
			unset($exploded[0]);
			$magicControl = implode("_", $exploded);

			if ($this->magicControlCreator->magicControlExist($magicControl)) {
				return new MagicControl(substr($name, strlen($magicControl) + 4), $this->magicControlCreator, $magicControl);
			}
		}

		return parent::createComponent($name);
	}

}
