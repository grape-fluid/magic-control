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
		$latte->addProvider('uiPresenter', $control->getPresenter());
		$latte->addProvider('snippetBridge', new \Nette\Bridges\ApplicationLatte\SnippetBridge($control));

		$translator = isset($control->translator) ? $control->translator : (isset($control->getPresenter()->translator) ? $control->getPresenter()->translator : null);
		$latte->addFilter('translate', function (\Latte\Runtime\FilterInfo $fi, ...$args) use ($translator) {
			return $translator ? $translator->translate(...$args) : $args[0];
		});

		$magicTemplate = new MagicTemplate($latte);
		$magicTemplate->addParam('control', $control);
		$magicTemplate->addParam('user', $control->getPresenter()->getUser());
		$magicTemplate->addParam('presenter', $control->getPresenter());
		$magicTemplate->addParam('basePath', $control->getPresenter()->getTemplate()->basePath);
		$magicTemplate->addParam('baseUri', $control->getPresenter()->getTemplate()->baseUri);

		return $magicTemplate;
	}

}
