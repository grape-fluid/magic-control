<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Nette\DI\Container;
use Nette\Http\Session;
use Nette\Http\SessionSection;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class Creator
{
	
	/** @var array */
	private $controls = [];
	
	/** @var Container */
	private $container;

	/** @var Cache */
	private $cache;

	/** @var SessionSection */
	private $sessionSection;

	/** @var Session */
	private $session;


	public function __construct(array $magicControls = [], Container $container = null , IStorage $IStorage = null, Session $session = null)
	{
		$this->container = $container;
		foreach ($magicControls AS $name => $options) {
			if (!isset($options['class'])) {
				throw new \LogicException("Control '$name' doesn't have specified 'class' key");
			}
			if (!preg_match("/[a-zA-Z]/", $name)) {
				throw new \LogicException("Component name must contain only a-z and A-Z, got $name");
			}
			if ($this->addMagicControl($name, $options['class'])) {
				$this->controls[$name] = $options;
			} else {
				# TODO: Catch other exception?
			}
		}

		$this->cache          = new Cache($IStorage, "grapeFluid.magicControl");
		$this->sessionSection = $session->getSection('grapeFluid.magicControl');
		$this->session        = $session;
	}


	/**
	 * @param string $name
	 * @param string $factoryClass
	 * @return bool
	 * @throws \LogicException
	 */
	public function addMagicControl($name, $factoryClass)
	{
		if (!class_exists($factoryClass)) {
			throw new \LogicException("Factory class '$name' for magic control not found.");
		} elseif (!in_array(IFactory::class, class_implements($factoryClass))) {
			throw new \LogicException("Factory class '$name' for magic control does't implement IFactory.");
		}
		return true;
	}
	
	
	/**
	 * @param string $name
	 * @return bool
	 */
	public function magicControlExist($name)
	{
		return isset($this->controls[$name]);
	}
	
	
	/**
	 * @param string $name
	 * @return string
	 */
	public function getFactoryClass($name)
	{
		return isset($this->controls[$name]['class']) ? $this->controls[$name]['class'] : null;
	}


	/**
	 * @param string $name
	 * @return \Nette\Application\UI\Control
	 */
	public function createMagicControl($name)
	{
		$class          = $this->getFactoryClass($name);
		$controlFactory = $this->container->getByType($class, FALSE);

		if (!$controlFactory) {
			$controlFactory = new $class;
		}
		$this->container->callInjects($controlFactory);

		$component = $controlFactory->create();
		$this->container->callInjects($component);

		return $component;
	}


	/**
	 * Vrátí seznam všech dostupných komponent
	 * @param $includeHidden bool - zahrnout i komponenty, ktere maji list: false
	 * @param bool $isInstanceOfMagicTemplate
	 * @return array
	 */
	public function getAllControls($includeHidden = false, $isInstanceOfMagicTemplate = false)
	{
		$allControls = $this->cache->load('all.controls', function (& $dependency) {
			$output  = [];
			foreach ($this->controls AS $name => $options) {

				$output[$name] = [
					'class'         => $options['class'],
					'params'        => isset($options['params']) ? $options['params'] : [],
					'list'          => isset($options['list']) ? $options['list'] : true,
					'desc'          => isset($options['desc']) ? $options['desc'] : null,
					'magicTemplate' => false,
					'templates' 	=> isset($options['templates']) ? $options['templates'] : [],
				];

				try {
					$mc = $this->createMagicControl($name);
					if ($mc instanceof IMagicTemplate) {
						$output[$name]['magicTemplate'] = true;
					}
				} catch (\Exception $e) {
					if (class_exists(substr($options['class'], 0, -7)) AND in_array(IMagicTemplate::class, class_implements(substr($options['class'], 0, -7)))) {
						$output[$name]['magicTemplate'] = true;
					}
				}

				foreach ($output[$name]['params'] AS $param) {
					if (!in_array($param[0], ["int", "integer", "float", "string", "bool", "boolean"])) {
						throw new \LogicException("Param '$param' of component '$name' have invalid type");
					}
				}
			}

			return $output;
		});

		foreach ($allControls AS $name => $options) {
			if (!$includeHidden AND !$options['list']) {
				unset($allControls[$name]);
				continue;
			} elseif ($isInstanceOfMagicTemplate AND !$options['magicTemplate']) {
				unset($allControls[$name]);
				continue;
			}
		}

		return $allControls;
	}


	/**
	 * @param string $controlName
	 * @return mixed|null
	 */
	public function getControlConfig($controlName)
	{
		$allControls = $this->getAllControls(true);
		if (key_exists($controlName, $allControls)) {
			return $allControls[$controlName];
		}
		
		return null;
	}


	/**
	 * @param string $name
	 * @param array $params
	 * @param string|null $templateName
	 */
	public function setControlParams($name, array $params, $templateName = null)
	{
		if ($this->session->isStarted()) {
			$this->sessionSection[$name] = [$params, $templateName];
		}
	}


	/**
	 * @param string $name
	 * @return array
	 */
	public function getControlParams($name)
	{
		return isset($this->sessionSection[$name]) ? $this->sessionSection[$name] : [[], null];
	}

}
