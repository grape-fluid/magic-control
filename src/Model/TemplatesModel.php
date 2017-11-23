<?php

namespace Grapesc\GrapeFluid\MagicControl\Model;

use Grapesc\GrapeFluid\Model\BaseModel;


class TemplatesModel extends BaseModel
{

	/**
	 * @inheritdoc
	 */
	public function getTableName()
	{
		return "mc_templates";
	}

}