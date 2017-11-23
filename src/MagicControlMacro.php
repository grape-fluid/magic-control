<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;


class MagicControlMacro extends MacroSet
{

	/**
	 * @param Compiler $compiler
	 * @return static
	 */
	public static function install(Compiler $compiler)
	{
		$m = new static($compiler);
		$m->addMacro('magicControl', array($m, 'magicControl'));
		return $m;
	}


	/**
	 * @param MacroNode $node
	 * @param PhpWriter $writer
	 * @return string
	 */
	public function magicControl(MacroNode $node, PhpWriter $writer)
	{
		//todo hashovat predavane argumenty a zrusit tak nutnost predavat unikatni nazev komponenty v parametrech
		$w = $writer->write('$_tmpComponentArgs = %node.array;');
		$w.= $writer->write('$_tmpComponentName = hash("crc32b", $_tmpComponentArgs[1][0] . (array_key_exists(2, $_tmpComponentArgs) ? $_tmpComponentArgs[2] : ""));');
		$w.= $writer->write('unset($_tmpComponentArgs[1][0]);');
		$w.= $writer->write('echo $this->global->uiControl->getComponent("mc_" . %node.word . "_" . $_tmpComponentName)->render($_tmpComponentArgs[1], array_key_exists(2, $_tmpComponentArgs) ? $_tmpComponentArgs[2] : null)');
		return $w;
	}
	
}