<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2023
 * @license   MIT License
 */

namespace JuniWalk\Utils\Enums;

enum Strategy: string
{
	case Major = 'major';
	case Minor = 'minor';
	case Patch = 'patch';
	case Build = 'build';
}
