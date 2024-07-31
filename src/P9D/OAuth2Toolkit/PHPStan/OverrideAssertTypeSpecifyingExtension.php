<?php

namespace P9D\OAuth2Toolkit\PHPStan;

use P9D\OAuth2Toolkit\OAuth2ToolkitAssert;
use PHPStan\Type\WebMozartAssert\AssertTypeSpecifyingExtension;

class OverrideAssertTypeSpecifyingExtension extends AssertTypeSpecifyingExtension
{

    public function getClass(): string
    {
        return OAuth2ToolkitAssert::class;
    }
}