<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Engine;
use Latte\Loaders\FileLoader;
use Latte\Loaders\StringLoader;
use Nette\Application\UI\ITemplate;


/**
 * @author Jiri Novy <novy@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class MagicTemplate implements ITemplate
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
	public function render()
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
	public function setFile($file)
	{
		$this->file = $file;
	}


	/**
	 * Returns the path to the template file.
	 * @return string|NULL
	 */
	public function getFile()
	{
		return $this->file;
	}

}
