<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2022
 * @license   MIT License
 */

namespace JuniWalk\Utils\Console\Input;

use Symfony\Component\Console\Input\ArrayInput as BaseInput;
use Symfony\Component\Console\Input\InputDefinition;

class ArrayInput extends BaseInput
{
	/**
	 * {@inheritdoc}
	 */
	public function __toString()
	{
		$input = parent::__toString();

		if (!$this->isInteractive()) {
			$input .= ' --no-interaction';
		}

		return trim($input);
	}


	/**
	 * {@inheritdoc}
	 */
	public function bind(InputDefinition $definition)
	{
		$this->definition = $definition;
		$this->parse();
	}
}
