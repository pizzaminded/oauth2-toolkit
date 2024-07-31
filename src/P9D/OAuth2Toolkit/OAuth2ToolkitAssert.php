<?php

namespace P9D\OAuth2Toolkit;

use P9D\OAuth2Toolkit\Exception\OAuth2ToolkitAssertionException;
use Webmozart\Assert\Assert;

class OAuth2ToolkitAssert extends Assert
{
    /**
     * @throws OAuth2ToolkitAssertionException
     */
    protected static function reportInvalidArgument($message)
    {
        throw new OAuth2ToolkitAssertionException($message);
    }
}