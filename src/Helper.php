<?php

namespace Grapesc\GrapeFluid\MagicControl;

use Latte\Engine;
use Latte\Loaders\StringLoader;
use Nette\Application\UI\Control;
use Nette\Application\UI\Presenter;
use Nette\ComponentModel\IComponent;


/**
 * @author Mira Jakes <jakes@grapesc.cz>
 */
class Helper
{

	/**
	 * Maximalni mozny pocet vnoreni komponenty se stejnymy parametry
	 * TODO: Setter, config?
	 * @var int $maxAttempts
	 */
	private static $maxAttempts = 5;

	/** @var array array */
	private static $attempts = [];


	/**
	 * Prevede magic makra v $text-u zapsane dle $pattern na pouzitelne komponenty
	 *
	 * @param $text
	 * @param IComponent $parent
	 * @param Engine|null $engine
	 * @param string $pattern
	 * @return mixed|string
	 */
	public static function magicMacroCreator($text, IComponent $parent, Engine $engine = null, $pattern = '~\[%([a-zA-Z]{0,50})\(([^%]*)\)(@[a-z_0-9]*)?%\]~')
	{
		//TODO kesovat vystup?

		if ($engine) {
			$latteEngine = $engine;
		}
		if ($parent instanceof Presenter) {
			$latteEngine = $parent->getTemplateFactory()->createTemplate($parent)->getLatte();
		} elseif ($parent instanceof Control) {
			$latteEngine = $parent->getPresenter()->getTemplateFactory()->createTemplate($parent->getPresenter())->getLatte();
		} else {
			//TODO Exception ?
		}

		$latteEngine->setLoader(new StringLoader());

		$count = preg_match_all($pattern, $text, $matches);

		if ($count) {
			foreach ($matches[0] AS $key => $match) {
				preg_match($pattern, $match, $macro);

				$name              = $macro[1];
				$arguments         = explode(",", $macro[2]);
				$tid               = "'" . hash('crc32b', serialize($macro) . $key . $parent->getName()) . "'";
				$templateName      = array_key_exists(3, $macro) ? ','.substr($macro[3], 1) : '';
				$magicControlMacro = "{magicControl $name [" . implode(",", array_merge([$tid], $arguments)) . "]$templateName}";
                $magicControlMacro = "<!-- START - $match -->" . $magicControlMacro . "<!-- END - $match -->";

				$suspect = $name . "_" . implode("_", $arguments);

				if ((isset(self::$attempts[$suspect]) ? self::$attempts[$suspect]++ : self::$attempts[$suspect] = 1) > self::$maxAttempts) {
					throw new \LogicException("Circular reference detected with control '$name' (args - " . implode(", ", $arguments) . ")");
				}

				$text = preg_replace( '/' . preg_quote($match, '/') . '/', $magicControlMacro, $text, 1);
			}

			return $latteEngine->renderToString($text);

		}
		
		return $text;
	}


	/**
	 * Vrati pocet nalezenych magic maker v $textu
	 *
	 * @param $text
	 * @param string $pattern
	 * @return int
	 */
	public static function containsMagicMacros($text, $pattern = '~\[%([a-zA-Z]{0,50})\(([^%]*)\)%\]~')
	{
		return preg_match_all($pattern, $text);
	}


    /**
     * Opak funkce magicMacroCreator, dynamicky vytvořený text nahradí za původní makra
     * Tím je možné ho uložit a zachovat konzistenci jako před výpisem
     *
     * Test - http://regexr.com/3f38t
     *
     * @param $text
     * @param string $pattern
     * @return mixed
     */
    public static function magicMacroRecreator($text, $pattern = '~<!-- START - (.*?) -->[\s\S]*?<!-- END - \1 -->~')
    {
        return preg_replace($pattern, '$1', $text);
    }


	/**
	 * TODO: vymyslet ne tak debilni nazev
	 */
	public static function createSafeEscapeString($input, $from = ["n:", "{", "}", "<?", "?>"], $to = ["&#110;&#58;", "&#123;", "&#125;", "&#60;&#63;", "&#63;&#62;"])
	{
		return str_replace($from, $to, $input);
	}
	
}
