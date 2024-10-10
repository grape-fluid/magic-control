<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Extension;


class MagicControlMacro extends Extension
{
	public function getTags(): array
	{
		return [
			'magicControl' => [MagicControlNode::class, 'create'],
		];
	}
}