<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Grapesc\GrapeFluid\MagicControl\Model\TemplatesModel;
use SplFileInfo;


/**
 * @author Kulisek Patrik <kulisek@grapesc.cz>
 * @author Mira Jakes <jakes@grapesc.cz>
 */
abstract class BaseMagicTemplateControl extends BaseMagicControl implements IMagicTemplate
{

	/** @var TemplateCacheService @inject */
	public $templateCacheService;

	/** @var TemplatesModel @inject*/
	public $templatesModel;

	/** @var string|null */
	protected $defaultTemplateFilename = null;


	/**
	 * return template source or null if not found
	 * @param string $magicControlName
	 * @param null $tid
	 * @return null|SplFileInfo
	 */
	public function getTemplateSource($magicControlName, $tid = null)
	{
		$template = $this->templateCacheService->load($magicControlName, $tid);

		//read template from storages if template not in cache
		if (!$template) {
			$template = $this->getTemplateSourceFromDB($magicControlName, $tid);

			if (!$template) {
				$template = $this->getTemplateSourceFromConfig($magicControlName, $tid);
			}

			if ($template) {
//				$template = $this->templateCacheService->saveStringAsFile($template, $magicControlName, $tid);
				if ($template instanceof \SplFileInfo) {
					$template = ['realPath' => $template->getRealPath()];
				}
				$this->templateCacheService->save($template, $magicControlName, $tid);
			} else {
				$this->templateCacheService->save(false, $magicControlName, $tid);
			}
		}

		if (is_array($template) AND key_exists('realPath', $template)) {
			return new \SplFileInfo($template['realPath']);
		}

		return $template ?: null;
	}


	/**
	 * @param $magicControlName
	 * @return SplFileInfo|void
	 */
	public function getDefaultTemplateSource($magicControlName)
	{
		if ($this->defaultTemplateFilename) {
			return new SplFileInfo($this->defaultTemplateFilename);
		}
	}


	/**
	 * @param string $magicControlName
	 * @param string $tid
	 * @return null|SplFileInfo
	 */
	protected function getTemplateSourceFromConfig($magicControlName, $tid)
	{
		$config = $this->magicControlCreator->getControlConfig($magicControlName);
		if ($config AND key_exists('templates', $config) AND key_exists($tid, $config['templates'])) {
			$file = $config['templates'][$tid];
			$filePath = null;
			if (is_file($file)) {
				$filePath = realpath($file);
			} else {
				$mcDir = dirname((new \ReflectionClass($this->magicControlCreator->createMagicControl($magicControlName)))->getFilename());

				if (is_file($mcDir . DIRECTORY_SEPARATOR . $file)) {
					$filePath = realpath($mcDir . DIRECTORY_SEPARATOR . $file);
				} elseif  (is_file($mcDir . DIRECTORY_SEPARATOR . 'templates'. DIRECTORY_SEPARATOR . $file)) {
					$filePath = realpath($mcDir . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $file);
				}
			}

			if ($filePath) {
				return new \SplFileInfo($filePath);
			}
		}

		return null;
	}


	/**
	 * load template by tid from storage
	 * @param $magicControlName
	 * @param $tid
	 * @return string|null
	 */
	protected function getTemplateSourceFromDB($magicControlName, $tid)
	{
		if (is_null($tid)) {
			$row = $this->templatesModel->getTableSelection()->where('magic_control = ? AND template_name IS NULL', $magicControlName)->fetch();
		} else {
			$row = $this->templatesModel->getTableSelection()->where('magic_control = ? AND template_name = ?', $magicControlName, $tid)->fetch();

		}
		return $row ? $row->source : null;
	}

}
