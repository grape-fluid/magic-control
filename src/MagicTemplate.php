<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Engine;
use Latte\Loaders\FileLoader;
use Latte\Loaders\StringLoader;
use Nette\Application\UI\Template;
use Nette\Bridges\ApplicationLatte\Template as NetteTemplate;

/**
 * @author Jiri Novy <novy@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class MagicTemplate extends NetteTemplate implements Template
{

	/** @var string */
	private $source;

	/** @var string */
	private $file;

	/** @var Engine */
	private $latte;

	/** @var array */
	private $params = [];


	/**
	 * MagicTemplate constructor.
	 * @param Engine $latte
	 */
	public function __construct(Engine $latte)
	{
		$this->latte = $latte;
		parent::__construct($latte);
	}


	/**
	 * @param string $name
	 * @param string $value
	 */
	public function __set($name, $value)
	{
		$this->addParam($name, $value);
	}


	/**
	 * @param $name
	 * @param $value
	 */
	public function addParam($name, $value)
	{
		$this->params[$name] = $value;
	}


	/**
	 * Renders template to output.
	 * @return void
	 */
	public function render(?string $file = null, array $params = []): void
	{
		$this->latte->setLoader($this->file ? new FileLoader() : new StringLoader());
		$this->latte->render($this->file ?: $this->source, $this->params);
	}


	/**
	 * @param string $source
	 */
	public function setSource($source)
	{
		$this->source = $source;
	}


	/**
	 * Sets the path to the template file.
	 * @param  string
	 * @return static
	 */
	public function setFile($file): static
	{
		$this->file = $file;
		return $this;
	}


}
