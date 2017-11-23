<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;


/**
 * @author Jiri Novy <novy@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class TemplateCacheService
{

	/** @var Cache */
	private $cache;

	/** @var IStorage */
	private $cacheStorage;


	/**
	 * TemplateCacheService constructor.
	 * @param IStorage $storage
	 */
	public function __construct(IStorage $storage)
	{
		$this->cacheStorage = $storage;
	}


	/**
	 * @param string $source
	 * @param string $magicControlName
	 * @param string $tid
	 * @return void
	 */
	public function save($source, $magicControlName, $tid)
	{
		$cache = $this->getTemplateCache();
		$cache->save($this->getCacheKey($magicControlName, $tid), $source);
	}


	/**
	 * @param string $magicControlName
	 * @param string $tid
	 * @return mixed
	 */
	public function load($magicControlName, $tid)
	{
		$cache = $this->getTemplateCache();
		return $cache->load($this->getCacheKey($magicControlName, $tid), false);
	}


	/**
	 * clear cache
	 * @param null|string $magicControlName
	 * @param null|string $tid
	 * @return void
	 */
	public function clearCache($magicControlName = null, $tid = null)
	{
		if (is_null($magicControlName)) {
			$this->getTemplateCache()->clean([Cache::ALL => Cache::ALL]);
		} else {
			$this->getTemplateCache()->remove($this->getCacheKey($magicControlName, $tid));
		}
	}


	/**
	 * @param string $magicControlName
	 * @param string $tid
	 * @return string
	 */
	private function getCacheKey($magicControlName, $tid)
	{
		return md5($magicControlName . $tid);
	}


	/**
	 * @return Cache
	 */
	private function getTemplateCache()
	{
		if (is_null($this->cache)) {
			$this->cache = new Cache($this->cacheStorage, 'magic-templates');
		}

		return $this->cache;
	}

}
