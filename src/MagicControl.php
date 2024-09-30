<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Grapesc\GrapeFluid\MagicControl\Exception\MagicTemplateException;
use Nette\Application\UI\Control;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class MagicControl extends Control
{

	/** @var bool */
	private static $redrawRequired = false;

	/** @var Creator */
	private $creator;

	/** @var string */
	private $componentName;

	/** @var string */
	private $magicControlName;
	
	/** @var IMagicControl|IMagicTemplate */
	private $component;

	/** @var bool */
	private $isStarted = false;

	/** @var bool */
	private $isRedrawRequired = false;


	/**
	 * MagicControl constructor.
	 * @param string $componentName
	 * @param Creator $creator
	 * @param string $magicControl
	 */
	public function __construct($componentName, Creator $creator, $magicControl)
	{
		$this->componentName    = $componentName;
		$this->creator          = $creator;
		$this->component        = $creator->createMagicControl($magicControl);
		$this->magicControlName = $magicControl;
		
		if (!in_array(IMagicControl::class, class_implements($this->component))) {
			throw new \LogicException("Magic control must implement \Grapesc\GrapeFluid\MagicControl\IMagicControl");
		}

		if(in_array(IMagicTemplate::class, class_implements($this->component))) {
			$this->component->setTemplateFactory(new MagicTemplateFactory());
		}

		$this->addComponent($this->component, $this->componentName);
	}


	/**
	 * @param array $params
	 * @param string|null $templateName
	 */
	public function render($params = [], $templateName = null)
	{
		if (!static::$redrawRequired || (static::$redrawRequired AND $this->isControlInvalid())) {

			if (!$this->isStarted) {
				$this->startup($params, $templateName);

				if ($this->component->getSnippetElement() AND !$this->isControlInvalid()) {
					$this->creator->setControlParams($this->componentName, $params, $templateName);
				}
			}

			$this->template->setFile(__DIR__ . '/template.latte');
			$this->template->component      = $this->component;
			$this->template->componentName  = $this->componentName;
			$this->template->snippetElement = $this->component->getSnippetElement();

			$this->template->render();
		}
	}


	/**
	 * Překreslí aktuální komponentu nebo jeji snippet
	 * @param string|null $snippet
	 */
	public function redrawComponent($snippet = null)
	{
		if ($this->presenter->isAjax()) {
			static::$redrawRequired = true;
			$this->isRedrawRequired = true;
			list($params, $templateName) = $this->creator->getControlParams($this->componentName);
			$this->startup($params, $templateName);
			if ($snippet) {
				$this->component->redrawControl($snippet);
			} else {
				$this->redrawControl();
			}
		}
	}


	/**
	 * @return bool
	 */
	public function isRedrawRequired()
	{
		return $this->isRedrawRequired;
	}


	/**
	 * @param array $params
	 * @param null $templateName
	 */
	private function startup(array|string $params = [], $templateName = null)
	{
		if (is_string($params)) {
			$params = [$params];
		}

		if (!$this->isStarted) {
			$this->component->setParams($params);
			$this->component->prepare();
			$this->loadMagicTemplate($templateName);
			$this->isStarted = true;
		}
	}


	/**
	 * for magic template
	 * @param string|null $templateName
	 */
	private function loadMagicTemplate($templateName = null)
	{
		if(in_array(IMagicTemplate::class, class_implements($this->component))) {
			$source = $this->component->getTemplateSource($this->magicControlName, $templateName);
			if (!$source) {
				$source = $this->component->getDefaultTemplateSource($this->magicControlName);
			}

			if ($source) {
				if ($source instanceof \SplFileInfo) {
					$this->component->getTemplate()->setFile($source->getPathName());
				} elseif (is_string($source)) {
					$this->component->getTemplate()->setSource($source);
				} else {
					throw new MagicTemplateException("Source must implement SplFileInfo or must be string, " . gettype($source) . " given");
				}
			} else {
				throw new MagicTemplateException("Template source for ". get_class($this->component) ." not found");
			}
		}
	}

}
