<?php

namespace ModulIS\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{}


class InvalidStateException extends \RuntimeException
{}


class InvalidPropertyDefinitionException extends InvalidStateException
{}


class MemberAccessException extends \LogicException
{}


class NotSupportedException extends \LogicException
{}
