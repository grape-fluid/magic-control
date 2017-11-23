<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Engine;
use Nette\Application\UI\Control;
use Nette\Application\UI\ITemplate;
use Nette\Application\UI\ITemplateFactory;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class MagicTemplateFactory implements ITemplateFactory
{

	/**
	 * @param Control|null $control
	 * @return ITemplate
	 */
	function createTemplate(Control $control = NULL)
	{
		/* @var $latte Engine */
		$latte = clone $control->getParent()->getTemplate()->getLatte();
		$latte->addProvider('uiControl', $control);

		$magicTemplate = new MagicTemplate($latte);
		$magicTemplate->addParam('control', $control);
		$magicTemplate->addParam('user', $control->getPresenter()->getUser());
		$magicTemplate->addParam('presenter', $control->getPresenter());

		return $magicTemplate;
	}

}
