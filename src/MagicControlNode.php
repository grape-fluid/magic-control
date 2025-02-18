<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Tag;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;


class MagicControlNode extends StatementNode
{

	public ExpressionNode $subject;

	public ArrayNode $args;

	public ?StringNode $extraParam = null;


	public static function create(Tag $tag): self
	{
		$node          = new self;
		$node->subject = $tag->parser->parseUnquotedStringOrExpression();
		$node->args    = $tag->parser->parseArguments();

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$_tmpComponentArgs = [%args];
        $_tmpComponentName = hash("crc32b", $_tmpComponentArgs[0][0] . ($_tmpComponentArgs[1] ?? null));
        unset($_tmpComponentArgs[0][0]);
        echo $this->global->uiControl->getComponent("mc_" . %node . "_" . $_tmpComponentName)->render($_tmpComponentArgs[0], $_tmpComponentArgs[1] ?? null);',
			$this->args->items,
			$this->subject,
			$this->subject,
		);
	}


	public function &getIterator(): \Generator
	{
		yield $this->subject;
		yield $this->args;
	}

}