<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Tag;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;


class MagicControlNode extends StatementNode
{

	public ExpressionNode $subject;

	public ArrayNode $args;

	public ?string $extraParam = null;


	public static function create(Tag $tag): self
	{
		$node          = new self;
		$node->subject = $tag->parser->parseUnquotedStringOrExpression();
		$node->args    = $tag->parser->parseArguments();

		if (preg_match('/\[(.*?)\](?:,([^,]+))?/', $tag->parser->text, $matches)) {
			$arrayString = trim($matches[1]);
			$arrayString = str_replace("'", '', $arrayString);
			$node->args->items = array_filter(array_map('trim', explode(',', $arrayString)));
			$param = isset($matches[2]) ? trim($matches[2]) : null;
			$node->extraParam = $param;
		}

		return $node;
	}


	public function print(PrintContext $context): string
	{
		return $context->format(
			'$_tmpComponentArgs = %dump;
        $_tmpComponentName = hash("crc32b", $_tmpComponentArgs[0] . (%dump ?: ""));
        unset($_tmpComponentArgs[0]);
        echo $this->global->uiControl->getComponent("mc_" . %node . "_" . $_tmpComponentName)->render($_tmpComponentArgs, %dump ?: null);',
			$this->args->items,
			$this->extraParam,
			$this->subject,
			$this->extraParam,
		);
	}

	public function &getIterator(): \Generator
	{
		yield $this->subject;
		yield $this->args;
		if ($this->extraParam !== null) {
			yield $this->extraParam;
		}
	}
}